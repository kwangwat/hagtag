<?php

class Admin_TranslateController extends Zend_Controller_Action {

    /**
     * Get system setting
     *
     * @param string $key           Configuration key to get
     * @param mixed|null $default   Default data to return if not valid
     * @return string               Configuration data
     */
    public function getSysSetting($key, $default = null) {
        return isset($this->view->sysSetting[$key]) ? $this->view->sysSetting[$key] : $default;
    }

    public function indexAction() {
        $params = $this->_getAllParams();
        $model = new Admin_Model_Translate();
        $sort = isset($params["_sort"]) ? $params["_sort"] : null;
        $sortType = isset($params["_sort_type"]) ? $params["_sort_type"] : null;
        $_keyword = isset($params["keyword"]) ? $params["keyword"] : null;
        $order = "msgid";
        $where = null;

        if ($_keyword) {
            $keyword = $model->getAdapter()->quote('%' . $_keyword . '%');
            $where .= "msgid LIKE {$keyword} OR msgstr LIKE {$keyword}";
        }
        if (isset($params["type_code"])) {
            if ($_keyword) {
                $where .=" AND ";
            }
            $where .= "language LIKE '%" . strtolower($params["type_code"]) . "%'";
        }
        $entries = $model->getTranslateList($where, $order);
        $page = $this->_getParam('pageClick', 1);

        $langAvalid = $this->getSysSetting(Ht_Model_SystemSetting::KEY_LANGUAGES_AVAILABLE, array());
        $language = (count($langAvalid) > 0) ? explode(",", $langAvalid) : array();

        $this->view->assign('language', $language);
        $this->view->assign('entries', $entries);
        $this->view->assign('params', $params);
        $this->view->assign('keyword', $_keyword);
    }

    public function editAction() {

        $id = $this->_getParam('id');
        $mode = $this->_getParam('mode');

        $model = new Admin_Model_Translate();
        $save = $this->_getParam('save');

        if ($save == 1) {
            // Do nothing
        } else {
            $this->_helper->layout->setLayout('layout_popup');
            $this->_helper->viewRenderer->setViewScriptPathSpec(':controller/new.:suffix');
            $language_arr = array();
            if ($mode) {
                $langAvalid = $this->getSysSetting(Ht_Model_SystemSetting::KEY_LANGUAGES_AVAILABLE, array());
                $language = (count($langAvalid) > 0) ? explode(",", $langAvalid) : array();//$model->getLanguage();
                $language_data = $model->getLanguageData($id);
                $code = $model->getLanguageDataById($id);

                foreach ($language as $item) {
                    $language_arr[$item]["name"] = $item;
                    foreach ($language_data as $item2) {
                        if ($item == $item2["language"]) {
                            $language_arr[$item]["data"] = $item2["msgstr"];
                        }
                    }
                }
                $this->view->assign('code', $code);
            }
            $this->view->assign('language', $language_arr);
            $this->view->assign('mode', $mode);
        }
    }

    public function searchListManageAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $model = new Admin_Model_Translate();
        $params = $this->_getAllParams();
        $data = $model->getDatalistSearch($params["path"]);
        if (is_array($data)) {
            echo join("\n", $data);
        }
    }

    public function saveDataAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();

        $data = $this->_getParam("data");
        $code = $data["t_code"];
        $model = new Admin_Model_Translate();
        foreach ($data["t_data"] as $key => $value) {
            if($value === "") {
                continue;
            }
            $model->setLanguageData($value, $key, $code);
        }

        /**
         * If checked `Compile on save` option
         */
        $compile = (int)$this->_request->getParam("compile", 0);
        if($compile > 0) {
            $this->_compile();
        }
    }

    public function deleteAction() {
        $this->_helper->layout->disableLayout();
        $this->_helper->viewRenderer->setNoRender();
        
        $params = $this->_getAllParams();
        $model = new Admin_Model_Translate();
        if(isset($params["id"])) {
            $model->deletedata($params["id"]);
        } else {
            foreach ($params["select"] as $item) {
                $model->deletedata($item);
            }
        }
        return;
    }

    public function complieAction() {
        $this->_helper->viewRenderer->setNoRender();
        $this->_helper->layout->disableLayout();
        $this->_compile();
    }

    protected function _compile() {

        $moConvert = new Ht_Utils_MoConverter();

        $langAvalid = $this->getSysSetting(Ht_Model_SystemSetting::KEY_LANGUAGES_AVAILABLE, array());
        $language = (count($langAvalid) > 0) ? explode(",", $langAvalid) : array();
        if (is_array($language)) {
            foreach ($language as $lang) {
                $local_dir = APPLICATION_PATH . '/languages/' . $lang . '/';
                if(!realpath($local_dir)) {
                    mkdir($local_dir, true);
                }
                $po_creater = new Ht_Utils_PoCreator(array(
                    Ht_Utils_PoCreator::LANGUAGE_KEY => $lang,
                    Ht_Utils_PoCreator::LOCAL_DIR_KEY => APPLICATION_PATH . '/languages/' . $lang . '/',
                    Ht_Utils_PoCreator::ADAPTER_KEY => new Zend_Db_Table(array(
                        'name' => 'sys_languages'
                    ))
                ));

                $po_creater->setProperties(array(
                    Ht_Utils_PoCreator::PROPERTY_CREATE_DATE => date("Y-m-d H:i:s"),
                    Ht_Utils_PoCreator::PROPERTY_REVISION_DATE => date("Y-m-d H:i:s"),
                    Ht_Utils_PoCreator::PROPERTY_PROJECT_ID => Ht_Utils_SystemSetting::getSetting(Ht_Model_SystemSetting::KEY_SOFTWARE_VERSION)
                ));
                $pofile = $po_creater->create();
                $moConvert->convert($pofile);
            }
        }
    }
}
