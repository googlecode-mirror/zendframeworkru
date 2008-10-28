--
-- Структура таблицы `site_acl_resources`
--

CREATE TABLE IF NOT EXISTS `site_acl_resources` (
  `id` varchar(32) collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `site_acl_roles`
--

CREATE TABLE IF NOT EXISTS `site_acl_roles` (
  `id` varchar(16) collate utf8_unicode_ci NOT NULL,
  `inherits` varchar(255) collate utf8_unicode_ci default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Структура таблицы `site_acl_rules`
--

CREATE TABLE IF NOT EXISTS `site_acl_rules` (
  `id` int(6) NOT NULL auto_increment,
  `roleId` varchar(16) collate utf8_unicode_ci NOT NULL,
  `resourceId` varchar(32) collate utf8_unicode_ci NOT NULL,
  `privileges` varchar(255) collate utf8_unicode_ci default NULL,
  `assert` varchar(64) collate utf8_unicode_ci default NULL,
  `type` enum('TYPE_ALLOW','TYPE_DENY') collate utf8_unicode_ci NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;