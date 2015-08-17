<?php
/**
 * System Setting Global data model
 */
class Ht_Model_SystemSetting {

    const KEY_SOFTWARE_VERSION               = "HT_software_version";
    const KEY_APPLICATION_NAME               = "HT_application_name";
    const KEY_APPLICATION_DESC               = "HT_application_desc";
    const KEY_META_AUTHOR                    = "HT_meta_author";
    const KEY_META_KEYWORDS                  = "HT_meta_keywords";
    const KEY_META_DESC                      = "HT_meta_description";
    const KEY_FOOTER_NOTES                   = "HT_footer_notes";
    const KEY_HOST_NAME                      = "HT_host_name";
    const KEY_CHARSET                        = "HT_charset";
    const KEY_CUSTOM_LOGO                    = "HT_custom_logo";
    const KEY_ACCESS_LOGGING                 = "HT_access_logging";
    const KEY_ALLOW_REMEMBER                 = "HT_allow_remember";
    const KEY_AUTH_MODE                      = "HT_auth_mode";
    const KEY_CRYPT_KEY                      = "HT_crypt_key";
    const KEY_SESSIONS_RESTRICT              = "HT_sessions_restrict";
    const KEY_DEFAULT_AUTH_SOURCE            = "HT_default_auth_source";
    const KEY_DEFAULT_AUTH_SOURCE_ID         = "HT_default_auth_source_id";
    const KEY_DEFAULT_LANGUAGE               = "HT_default_language";
    const KEY_DEFAULT_TEMPLATE               = "HT_default_template";
    const KEY_DEFAULT_PAGE                   = "HT_redirect_login";
    const KEY_DEFAULT_ADMIN_PAGE             = "HT_redirect_admin_login";
    const KEY_LANGUAGES_AVAILABLE            = "HT_languages_available";
    const KEY_NUM_LIST_PER_PAGE              = "HT_num_list_per_page";
    const KEY_DEFAULT_UPLOAD_DIR             = "HT_default_upload_directory";
    const KEY_CMOD_DIR                       = "HT_cmod_dir";
    const KEY_CMOD_FILE                      = "HT_cmod_file";
    const KEY_404_ERROR_PAGE                 = "HT_404_error_page";
    const KEY_404_ERROR_PAGE_MSG             = "HT_404_error_page_message";
    const KEY_DATE_FORMAT                    = "HT_date_format";
    const KEY_DATE_FORMAT_SHORT              = "HT_date_format_short";
    const KEY_SYSTEM_DOWN                    = "HT_system_down";
    const KEY_SYSTEM_DOWN_MESSAGE            = "HT_system_down_message";
    const KEY_SYSTEM_DOWN_COMMING_ON_TIME    = "HT_system_down_comming_on_time";
    const KEY_SYSTEM_DOWN_MESSAGE_COMMING_ON = "HT_system_down_message_comming_on";
    const KEY_SYSTEM_LOGGING                 = "HT_system_logging";
    const KEY_SYSTEM_LOGGING_TYPE            = "HT_system_logging_type";
    const KEY_SYSTEM_LOGGING_PATH            = "HT_system_logging_path";
    const KEY_USER_CAN_CHANGE_PROFILE        = "HT_user_can_change_profile";
    const KEY_SYSTEM_TIMEZONE                = "HT_system_timezone";
    const KEY_WORKING_TIME_START             = "HT_working_time_start";
    const KEY_WORKING_TIME_END               = "HT_working_time_end";
    const KEY_SENDMAIL_PATH                  = "HT_sendmail_path";
    const KEY_SMTP_AUTH                      = "HT_smtp_auth";
    const KEY_SMTP_HOST                      = "HT_smtp_host";
    const KEY_SMTP_PASSWORD                  = "HT_smtp_password";
    const KEY_SMTP_PORT                      = "HT_smtp_port";
    const KEY_SMTP_SECURE                    = "HT_smtp_secure";
    const KEY_SMTP_TIMEOUT                   = "HT_smtp_timeout";
    const KEY_SMTP_USERNAME                  = "HT_smtp_username";
    const KEY_WEBSERVICE_REALM_NAME          = "HT_webservice_realm_name";
    /**
     * Setting key
     *
     * @var string
     */
    protected $_setting_name;

    /**
     * Setting value
     *
     * @var string
     */
    protected $_setting_value;

    /**
     * Setting last update time
     *
     * @var datetime
     */
    protected $_setting_updated;


    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public static function getAdapter() {
        return Zend_Db_Table_Abstract::getDefaultAdapter();
    }

    public static function update($key, $value) {
        $table = new Ht_Model_DbTable_SystemSetting();
        $record = $table->fetchRow("setting_name='{$key}'");
        if(!$record) {
            $record = $table->fetchNew();
        }
        $record->setFromArray(array(
            "setting_name"  => $key,
            "setting_value" => $value
        ));
        return $record->save();
    }
}
