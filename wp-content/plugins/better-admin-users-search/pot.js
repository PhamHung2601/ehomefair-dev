const wpPot = require('wp-pot');

wpPot({
  destFile: 'i18n/baus.pot',
  domain: 'baus',
  package: 'Better Admin Users Search',
  src: ['better-admin/users-search.php', 'includes/*.php'],
  lastTranslator: 'Applelo<boubaultlois@gmail.com>',
  bugReport: 'http://wordpress.org/support/plugin/better-admin-users-search'
});