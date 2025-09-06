
import { io } from 'socket.io-client';

export const useChatStore = defineStore('chat', {
  // ℹ️ arrow function recommended for full type inference
  state: () => ({
    contacts: [],
    chatsContacts: [],
    profileUser: undefined,
    activeChat: null,
    accessToken: useCookie('accessToken'),
    userData: useCookie('userData'),
    pusher: null,
    isSending:false,
    msgError:'',
    isError:false,
    countNewMessage: 0,
    socket:null,
    pageList: 1,
    hasMoreList: true,
    isLoadingList: false,
    pageMessage: 1,
    hasMoreMessages: true,
    selectedTab: 'all',
  }),
  actions: {
    async initPusher() {
      if (!this.socket) {
        // 🔐 Setup Socket.IO client dengan token Authorization
        this.socket = io(import.meta.env.VITE_SOCKET_URL || 'http://localhost:3000', {
          auth: {
            token: this.accessToken
          },
          transports: ['websocket'],
          reconnection: true,
        });

        // ✅ Join ke channel private
        const channelName = `company-${this.userData.company_id}-users-${this.userData.id}`;
        this.socket.emit('join', channelName);

        // 📥 LIST MESSAGE
        this.socket.on('list-message', (data) => {
          const incomingContacts = Array.isArray(data) ? data : [data];

          incomingContacts.forEach(newContact => {
            let standardCreatedAt = newContact.created_at || new Date().toISOString();

            // Normalisasi created_at
            if (typeof standardCreatedAt === 'string') {
              if (standardCreatedAt.includes(' ') && !standardCreatedAt.includes('T')) {
                standardCreatedAt = standardCreatedAt.replace(' ', 'T');
              }
              if (!standardCreatedAt.includes('.')) {
                standardCreatedAt = standardCreatedAt + '.000000Z';
              }
              if (!standardCreatedAt.endsWith('Z')) {
                standardCreatedAt = standardCreatedAt + 'Z';
              }
            }

            const standardizedContact = {
              whatsapp_users_id: newContact.whatsapp_users_id || '',
              whatsapp_users_username: newContact.whatsapp_users_username || '',
              message_text: newContact.message_text || '',
              room_id: parseInt(newContact.room_id || '0', 0),
              created_at: standardCreatedAt,
              unread_messages: newContact.unread_messages || 0,
              wa_bot_id: newContact.wa_bot_id || 0,
              wa_bot_name: newContact.wa_bot_name || '',
              whatsapp_bot_id: newContact.whatsapp_bot_id || 0
            }

            // Cari kontak apakah sudah ada
            const index = this.chatsContacts.findIndex(
              c => c.whatsapp_users_id === standardizedContact.whatsapp_users_id
            );

            if (index !== -1) {
              // Hapus yang lama
              this.chatsContacts.splice(index, 1);
            }

            // Masukkan ke atas
            this.chatsContacts.unshift(standardizedContact);
          });

          this.chatsContacts.sort((a, b) => {
            return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
          });
        });

        // 📥 DETAIL MESSAGE
        this.socket.on('detail-message', (data) => {
          
          if (!this.activeChat || !Array.isArray(this.activeChat.data_message)) return

          const newMessage = Array.isArray(data) ? data[0] : data

          // Cek apakah whatsapp_message_users_id sama
          if (this.activeChat.data_users.whatsapp_users_id !== newMessage.whatsapp_message_users_id) {
            return // ❌ Beda user, abaikan
          }

          // Cari apakah pesan sudah ada di data_message
          const existingIndex = this.activeChat.data_message.findIndex(
            msg => msg.whatsapp_message_id_chat === newMessage.whatsapp_message_id_chat
          )

          if (existingIndex === -1) {
            // Pesan belum ada → tambahkan
            this.activeChat.data_message.push(newMessage)
            this.countNewMessage++
          } else {
            // Pesan sudah ada → update datanya
            this.activeChat.data_message[existingIndex] = {
              ...this.activeChat.data_message[existingIndex],
              ...newMessage,
            }
          }
        });

        // 📥 ACTIVE USERS
        this.socket.on('active-users-message', (data) => {
          const newData = JSON.parse(JSON.stringify(data));
          this.activeChat.data_users = newData;
        });

        this.socket.on('connect', () => {
          console.log('🟢 Connected to socket server');
        });

        this.socket.on('disconnect', () => {
          console.warn('🔴 Disconnected from socket server');
        });
      }
    },

    async fetchChatsAndContacts({ q = '', loadMore = false, status = 'all' } = {}) {
      if (this.isLoadingList || (!this.hasMoreList && loadMore)) return

      this.isLoadingList = true

      try {
        const response = await useApi(createUrl('/list_chat', {
          query: {
            q,
            page: this.pageList,
            status,
          },
        }), {
          method: 'POST',
          headers: {
            Authorization: `Bearer ${this.accessToken.value}`,
            'Content-Type': 'application/json',
          },
        })

        const newData = response.data.value.data || []
        const oldLength = this.chatsContacts.length

        if (loadMore) {
          this.chatsContacts = [...this.chatsContacts, ...newData]
        } else {
          this.chatsContacts = newData
        }

        // ✅ Guard tambahan: jika tidak ada data baru, set hasMoreList ke false
        if (newData.length === 0 || this.chatsContacts.length === oldLength) {
          this.hasMoreList = false
        } else {
          this.pageList++
        }

        if (!loadMore) {
          this.profileUser = {
            name: 'Admin',
            status: 'online',
          }
        }

      } catch (error) {
        console.error('Error fetching chat contacts:', error)
      } finally {
        this.isLoadingList = false
      }
    },

    async getChat(data) {

      this.pageMessage = 1
      this.hasMoreMessages = true
      
      this.activeChat = {
        data_users: {
          bot_id :  data.wa_bot_id,
          company_id : 0,
          created_at : '',
          id : 0,
          updated_at : "",
          wa_bot_name : data.wa_bot_name,
          whatsapp_users_id : data.whatsapp_users_id,
          whatsapp_users_owner : 0,
          whatsapp_users_username : data.whatsapp_users_username,
          room_id: data.room_id
        },
        data_message: [], // kosong dulu
      }

      try {
        const res = await useApi('/list_message', {
          method: 'POST',
          body: JSON.stringify({
            whatsapp_users:data.whatsapp_users_id,
            room_id:data.room_id,
            bot_id:data.wa_bot_id
          }),
          headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${this.accessToken.value}`,
          },
        });
  
        if (res.statusCode.value === 200) {
          await nextTick(() => { 
            this.activeChat.data_message = res.data.value.data_message || []
            this.activeChat.data_users = {
              ...res.data.value.data_users,
              room_id: data.room_id
            }
          });
        } 
  
      } catch (err) {
        console.error(err);
      }

    },

    async loadMoreMessages(data) {
      if (!this.hasMoreMessages) return

      this.pageMessage++
      const res = await this.fetchMessages(data, this.pageMessage)

      if (res.success) {
        if (res.data.length === 0) {
          this.hasMoreMessages = false
        } else {
          this.activeChat.data_message = [
            ...res.data,
            ...this.activeChat.data_message
          ]
        }
      }
    },

    async fetchMessages(data, page) {
      try {
        const res = await useApi('/list_message', {
          method: 'POST',
          body: JSON.stringify({ ...data, page }),
          headers: {
            'Authorization': `Bearer ${this.accessToken.value}`,
            'Content-Type': 'application/json',
          },
        });

        return {
          success: res.statusCode.value === 200,
          data: res.data.value.data_message || [],
        }
      } catch (err) {
        console.error('Error fetching messages:', err)
        return { success: false, data: [] }
      }
    },

    async sendMsg({ message, file = null }) {

      this.isSending = true;

      let keyID = '';
      const contactId = this.chatsContacts.find((c,k) => {
        if (this.activeChat)
          if (c.whatsapp_users_id === this.activeChat.data_users.whatsapp_users_id) {
            keyID = k;
          }
          return c.whatsapp_users_id === this.activeChat.data_users.whatsapp_users_id
        
        return false
      })

      let bot_id = contactId.whatsapp_bot_id;
      let whatsapp_users = contactId.whatsapp_users_id;
      let room_id = contactId.room_id;

      try {
        let res;
    
        if (file) {
          // 🔄 Jika ada file → gunakan FormData
          const formData = new FormData();
          formData.append('bot_id', bot_id);
          formData.append('whatsapp_users', whatsapp_users);
          formData.append('room_id', room_id);
          formData.append('message', message || '');
          formData.append('file', file);
    
          res = await $api('/send_message', {
            method: 'POST',
            body: formData,
            headers: {
              'Authorization': `Bearer ${this.accessToken.value}`,
              'Accept': 'application/json'
            },
          });
          res = await res.json(); // convert ke JSON
        } else {
          // 🔄 Jika tanpa file → JSON biasa
          res = await useApi('/send_message', {
            method: 'POST',
            body: JSON.stringify({
              bot_id,
              whatsapp_users,
              room_id,
              message,
            }),
            headers: {
              'Content-Type': 'application/json',
              'Authorization': `Bearer ${this.accessToken.value}`,
            },
          });
        }
  
        if (res.statusCode.value === 200) {
          await nextTick(() => { 

            this.isSending = false;

            const errorMessage = res.data?.value?.data_send_message?.errors?.single?.message

            if (errorMessage) {
              this.isError = true;
              this.msgError = errorMessage;
              return;
            }


            let data = {
              "whatsapp_message_text": res.data.value.data_send_message.data[0].whatsapp_message_text,
              "whatsapp_message_status_user": res.data.value.data_send_message.data[0].whatsapp_message_status_user,
              "whatsapp_bot_id": bot_id
            }

            this.activeChat?.data_message.push(data)
 
            let standardCreatedAt = res.data.value.data_send_message.data[0].created_at;
            
            // Ensure created_at is in ISO string format (with milliseconds)
            if (standardCreatedAt) {
              // Add milliseconds if missing
              if (!standardCreatedAt.includes('.')) {
                standardCreatedAt = standardCreatedAt.replace(' ', 'T') + '.000000Z';
              }
              // Replace space with T if in MySQL format
              if (standardCreatedAt.includes(' ') && !standardCreatedAt.includes('T')) {
                standardCreatedAt = standardCreatedAt.replace(' ', 'T');
              }
              // Ensure Z at the end for UTC
              if (!standardCreatedAt.endsWith('Z')) {
                standardCreatedAt = standardCreatedAt.endsWith('Z') ? standardCreatedAt : standardCreatedAt + 'Z';
              }
            } else {
              // If no created_at, use current time
              standardCreatedAt = new Date().toISOString();
            }
            
            // Hapus kontak lama dari list (jika ada)
            this.chatsContacts = this.chatsContacts.filter(c =>
              c.whatsapp_users_id !== contactId.whatsapp_users_id
            );

            // Push data baru yang sudah lengkap
            this.chatsContacts.push({
              message_text: message,
              created_at: standardCreatedAt,
              room_id: res.data.value.data_send_message.data[0].id,
              whatsapp_bot_id: bot_id,
              unread_messages: 0,
              whatsapp_users_id: contactId.whatsapp_users_id,
              whatsapp_users_username: contactId.whatsapp_users_username,
              wa_bot_id: contactId.wa_bot_id,
              wa_bot_name: contactId.wa_bot_name,
            });

            // Sort kembali berdasarkan waktu terbaru
            this.chatsContacts.sort((a, b) => {
              return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
            });
          });
        }
  
      } catch (err) {
        console.error(err);
      }
    },
  },
})
