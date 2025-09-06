export default [
  {
    title: 'Dashboard',
    icon: { icon: 'tabler-smart-home' },
    to: 'dashboards-crm',
  },
  {
    title: 'Whatsapp Integration',
    icon: { icon: 'tabler-brand-whatsapp' },
    to: 'apps-whatsapp-whatsapp',
  },
  {
    title: 'Chat',
    icon: { icon: 'tabler-message-circle-2' },
    to: 'apps-chat',
  },
  {
    title: 'Contacts',
    icon: { icon: 'tabler-address-book' },
    to: 'apps-contacts-list',
  },
  {
    title: 'Import',
    icon: { icon: 'tabler-database-import' },
    to: 'apps-import-list',
  },
  {
    title: 'Receiver',
    icon: { icon: 'tabler-list-details' },
    to: 'apps-receiver-list',
  },
  {
    title: 'Template',
    icon: { icon: 'tabler-template' },
    to: 'apps-template-list',
  },
  {
    title: 'Broadcast',
    icon: { icon: 'tabler-broadcast' },
    to: 'apps-broadcast-list',
  },
  {
    title: 'Users',
    icon: { icon: 'tabler-user' },
    to: 'apps-user-list',
  },
  {
    title: 'Information',
    icon: { icon: 'tabler-user' },
    children: [
      { title: 'Company Settings', to: { name: 'pages-company-settings-tab', params: { tab: 'account' } } },
    ],
  },
]
