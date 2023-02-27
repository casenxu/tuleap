DROP TABLE IF EXISTS plugin_git_post_receive_mail;
DROP TABLE IF EXISTS plugin_git_post_receive_notification_user;
DROP TABLE IF EXISTS plugin_git_post_receive_notification_ugroup;
DROP TABLE IF EXISTS plugin_git_log;
DROP TABLE IF EXISTS plugin_git_ci;
DROP TABLE IF EXISTS plugin_git;
DROP TABLE IF EXISTS plugin_git_remote_servers;
DROP TABLE IF EXISTS plugin_git_remote_ugroups;
DROP TABLE IF EXISTS plugin_git_housekeeping;
DROP TABLE IF EXISTS plugin_git_gerrit_config_template;
DROP TABLE IF EXISTS plugin_git_webhook_url;
DROP TABLE IF EXISTS plugin_git_webhook_log;
DROP TABLE IF EXISTS plugin_git_repository_fine_grained_permissions_enabled;
DROP TABLE IF EXISTS plugin_git_full_history;
DROP TABLE IF EXISTS plugin_git_full_history_checkpoint;
DROP TABLE IF EXISTS plugin_git_log_read_daily;
DROP TABLE IF EXISTS plugin_git_file_logs_parse;
DROP TABLE IF EXISTS plugin_git_fine_grained_regexp_enabled;
DROP TABLE IF EXISTS plugin_git_repository_fine_grained_regexp_enabled;
DROP TABLE IF EXISTS plugin_git_default_fine_grained_regexp_enabled;
DROP TABLE IF EXISTS plugin_git_restricted_gerrit_servers;
DROP TABLE IF EXISTS plugin_git_restricted_gerrit_servers_allowed_projects;
DROP TABLE IF EXISTS plugin_git_global_parameters;
DROP TABLE IF EXISTS plugin_git_big_object_authorized_project;
DROP TABLE IF EXISTS plugin_git_commit_status;
DROP TABLE IF EXISTS plugin_git_default_fine_grained_permissions;
DROP TABLE IF EXISTS plugin_git_default_fine_grained_permissions_enabled;
DROP TABLE IF EXISTS plugin_git_default_fine_grained_permissions_writers;
DROP TABLE IF EXISTS plugin_git_default_fine_grained_permissions_rewinders;
DROP TABLE IF EXISTS plugin_git_repository_fine_grained_permissions;
DROP TABLE IF EXISTS plugin_git_repository_fine_grained_permissions_writers;
DROP TABLE IF EXISTS plugin_git_repository_fine_grained_permissions_rewinders;
DROP TABLE IF EXISTS plugin_git_commit_details_cache;
DROP TABLE IF EXISTS plugin_git_change_build_status_permissions;

DELETE FROM service WHERE short_name='plugin_git';
DELETE FROM reference_group WHERE reference_id=30;
DELETE FROM reference WHERE id=30;
DELETE FROM reference_group WHERE reference_id=33;
DELETE FROM reference WHERE id=33;
DELETE FROM user_access_key_scope WHERE scope_key = 'write:git_repository';

DELETE FROM permissions_values WHERE permission_type IN ('PLUGIN_GIT_READ', 'PLUGIN_GIT_WRITE', 'PLUGIN_GIT_WPLUS', 'PLUGIN_GIT_ADMIN');

DELETE FROM forgeconfig WHERE name = 'feature_flag_enable_pre_receive_command';
