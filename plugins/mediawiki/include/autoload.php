<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload6a596b04161eda0f5f8df7709bad4042($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'mediawiki_migration_mediawikimigrator' => '/Migration/MediawikiMigrator.php',
            'mediawiki_unsupportedlanguageexception' => '/UnsupportedLanguageException.php',
            'mediawiki_unsupportedversionexception' => '/UnsupportedVersionException.php',
            'mediawikiadmincontroller' => '/MediawikiAdminController.class.php',
            'mediawikiadminlanguagepanepresenter' => '/MediawikiAdminLanguagePanePresenter.php',
            'mediawikiadminpanepresenter' => '/MediawikiAdminPanePresenter.php',
            'mediawikiadminpermissionspanepresenter' => '/MediawikiAdminPermissionsPanePresenter.class.php',
            'mediawikidao' => '/MediawikiDao.class.php',
            'mediawikifusionforgeprojectnameretriever' => '/MediawikiFusionForgeProjectNameRetriever.php',
            'mediawikigrouppresenter' => '/MediawikiGroupPresenter.class.php',
            'mediawikigroups' => '/MediawikiGroups.class.php',
            'mediawikiinstantiater' => '/MediawikiInstantiater.class.php',
            'mediawikiinstantiaterexception' => '/MediawikiInstantiaterException.class.php',
            'mediawikilanguagedao' => '/MediawikiLanguageDao.php',
            'mediawikilanguagemanager' => '/MediawikiLanguageManager.php',
            'mediawikimanager' => '/MediawikiManager.class.php',
            'mediawikimlebextensiondao' => '/MediawikiMLEBExtensionDao.php',
            'mediawikimlebextensionmanager' => '/MediawikiMLEBExtensionManager.php',
            'mediawikimlebextensionmanagerloader' => '/MediawikiMLEBExtensionManagerLoader.php',
            'mediawikiplugin' => '/mediawikiPlugin.class.php',
            'mediawikiplugindescriptor' => '/MediaWikiPluginDescriptor.class.php',
            'mediawikiplugininfo' => '/MediaWikiPluginInfo.class.php',
            'mediawikisiteadminallowedprojectspresenter' => '/MediawikiSiteAdminAllowedProjectsPresenter.class.php',
            'mediawikisiteadmincontroller' => '/MediawikiSiteAdminController.class.php',
            'mediawikisiteadminresourcerestrictor' => '/MediawikiSiteAdminResourceRestrictor.php',
            'mediawikisiteadminresourcerestrictordao' => '/MediawikiSiteAdminResourceRestrictorDao.php',
            'mediawikiusergroupsmapper' => '/MediawikiUserGroupsMapper.class.php',
            'mediawikiversiondao' => '/MediawikiVersionDao.php',
            'mediawikiversionmanager' => '/MediawikiVersionManager.php',
            'mediawikixmlimporter' => '/MediaWikiXMLImporter.class.php',
            'pluginspecificrolesetting' => '/PluginSpecificRoleSettings.php',
            'servicemediawiki' => '/ServiceMediawiki.class.php',
            'systemevent_mediawiki_switch_to_123' => '/events/SytemEvent_MEDIAWIKI_SWITCH_TO_123.class.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload6a596b04161eda0f5f8df7709bad4042');
// @codeCoverageIgnoreEnd
