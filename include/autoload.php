<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart
// this is an autogenerated file - do not edit
function autoload0a70b14d27b945283621ff610154d2c1($class) {
    static $classes = null;
    if ($classes === null) {
        $classes = array(
            'mytuleap_contact_supportplugin' => '/mytuleap_contact_supportPlugin.class.php',
            'tuleap\\mytuleapcontactsupport\\contactsupportcontroller' => '/ContactSupportController.php',
            'tuleap\\mytuleapcontactsupport\\emailsendexception' => '/EmailSendException.php',
            'tuleap\\mytuleapcontactsupport\\plugin\\descriptor' => '/Plugin/Descriptor.class.php',
            'tuleap\\mytuleapcontactsupport\\plugin\\info' => '/Plugin/Info.class.php',
            'tuleap\\mytuleapcontactsupport\\presenter\\confirmationemailtouserpresenter' => '/Presenter/ConfirmationEmailToUserPresenter.php',
            'tuleap\\mytuleapcontactsupport\\presenter\\emailtosupportpresenter' => '/Presenter/EmailToSupportPresenter.php',
            'tuleap\\mytuleapcontactsupport\\presenter\\formpresenter' => '/Presenter/FormPresenter.php',
            'tuleap\\mytuleapcontactsupport\\presenter\\modalpresenter' => '/Presenter/ModalPresenter.php',
            'tuleap\\mytuleapcontactsupport\\router' => '/Router.php'
        );
    }
    $cn = strtolower($class);
    if (isset($classes[$cn])) {
        require dirname(__FILE__) . $classes[$cn];
    }
}
spl_autoload_register('autoload0a70b14d27b945283621ff610154d2c1');
// @codeCoverageIgnoreEnd
