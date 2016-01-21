<?php
/**
 * Copyright (c) Enalean, 2012 - 2015. All Rights Reserved.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 */

class SVN_Apache_SvnrootConfTestEventManager extends EventManager {
    public function processEvent($event_name, $params) {
        $project_row = array();

        $params['svn_apache_auth'] = null;
    }
}

require_once 'common/svn/SVN_Apache_SvnrootConf.class.php';

mock::generate('EventManager');

class SVN_Apache_SvnrootConfTest extends TuleapTestCase {

    function setUp() {
        ForgeConfig::store();
        $GLOBALS['sys_name']   = 'Platform';
        $GLOBALS['sys_dbhost'] = 'db_server';
        $GLOBALS['sys_dbname'] = 'db';
        $GLOBALS['svn_prefix'] = '/bla';
        $GLOBALS['sys_dbauth_user']   = 'dbauth_user';
        $GLOBALS['sys_dbauth_passwd'] = 'dbauth_passwd';
    }

    function tearDown() {
        ForgeConfig::restore();
        unset($GLOBALS['sys_name']);
        unset($GLOBALS['sys_dbname']);
        unset($GLOBALS['sys_dbhost']);
        unset($GLOBALS['svn_prefix']);
        unset($GLOBALS['sys_dbauth_user']);
        unset($GLOBALS['sys_dbauth_passwd']);
    }

    /**
     * @return SVN_Apache_SvnrootConf
     */
    private function GivenSvnrootForTwoGroups($authentification_mode) {
        $projects = array(array('repository_name' => 'gpig',
                                'group_name'      => 'Guinea Pig',
                                'group_id'        => 101,
                                'auth_mod'        => $authentification_mode),
                          array('repository_name' => 'garden',
                                'group_name'      => 'The Garden Project',
                                'group_id'        => 102,
                                'auth_mod'        => $authentification_mode));
        $repositories = array();

        $project_manager = mock('ProjectManager');
        $event_manager   = new SVN_Apache_SvnrootConfTestEventManager();
        $token_manager   = mock('SVN_TokenUsageManager');

        $factory = new SVN_Apache_Auth_Factory($project_manager, $event_manager, $token_manager);

        return new SVN_Apache_SvnrootConf($factory, $projects, $repositories);
    }

    private function GivenAFullApacheConfWithModMysql() {
        $backend = $this->GivenSvnrootForTwoGroups('modmysql');
        return $backend->getFullConf();
    }

    function testFullConfShouldWrapEveryThing() {
        $conf = $this->GivenAFullApacheConfWithModMysql();
        //echo '<pre>'.htmlentities($conf).'</pre>';

        $this->assertNoPattern('/PerlLoadModule Apache::Tuleap/', $conf);
        $this->assertPattern('/AuthMYSQLEnable/', $conf);
        $this->ThenThereAreTwoLocationDefinedGpigAndGarden($conf);
        $this->ThenThereAreOnlyOneCustomLogStatement($conf);
    }

    private function ThenThereAreTwoLocationDefinedGpigAndGarden($conf) {
        $matches = array();
        preg_match_all('%<Location /svnroot/([^>]*)>%', $conf, $matches);
        $this->assertEqual($matches[1][0], 'gpig');
        $this->assertEqual($matches[1][1], 'garden');
    }

    private function ThenThereAreOnlyOneCustomLogStatement($conf) {
        preg_match_all('/CustomLog/', $conf, $matches);
        $this->assertEqual(1, count($matches[0]));
    }

    function GivenAFullApacheConfWithModPerl() {
        $svnroot = $this->GivenSvnrootForTwoGroups('modperl');
        return $svnroot->getFullConf();
    }

    function testFullApacheConfWithModPerl() {
        $conf = $this->GivenAFullApacheConfWithModPerl();
        //echo '<pre>'.htmlentities($conf).'</pre>';

        $this->assertPattern('/PerlLoadModule Apache::Tuleap/', $conf);
        $this->assertNoPattern('/AuthMYSQLEnable/', $conf);
        $this->ThenThereAreTwoLocationDefinedGpigAndGarden($conf);
        $this->ThenThereAreOnlyOneCustomLogStatement($conf);
    }

    public function itHasALogFileFromConfiguration() {
        ForgeConfig::store();
        ForgeConfig::set(SVN_Apache_SvnrootConf::CONFIG_SVN_LOG_PATH, '${APACHE_LOG_DIR}/tuleap_svn.log');

        $conf = $this->GivenAFullApacheConfWithModPerl();
        $this->assertPattern('%\${APACHE_LOG_DIR}/tuleap_svn\.log%', $conf);

        ForgeConfig::restore();
    }
}