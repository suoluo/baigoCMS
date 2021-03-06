<?php
/*-----------------------------------------------------------------
！！！！警告！！！！
以下为系统文件，请勿修改
-----------------------------------------------------------------*/

//不能非法包含或直接执行
if (!defined("IN_BAIGO")) {
    exit("Access Denied");
}

/*-------------用户类-------------*/
class MODEL_SPEC {

    private $obj_db;
    private $is_magic;
    public $specStatus = array();

    function __construct() { //构造函数
        $this->obj_db   = $GLOBALS["obj_db"]; //设置数据库对象
        $this->is_magic = get_magic_quotes_gpc();

        if (BG_MODULE_FTP > 0 && defined("BG_SPEC_FTPHOST") && !fn_isEmpty(BG_SPEC_FTPHOST)) {
            if (defined("BG_SPEC_URL") && !fn_isEmpty(BG_SPEC_URL)) {
                $this->attachPre = BG_SPEC_URL . "/";
            } else {
                $this->attachPre = BG_URL_ROOT;
            }
        } else {
            $this->attachPre = BG_URL_ROOT;
        }
    }


    function mdl_create_table() {
        foreach ($this->specStatus as $_key=>$_value) {
            $_arr_status[] = $_key;
        }
        $_str_status = implode("','", $_arr_status);

        $_arr_specCreat = array(
            "spec_id"           => "int NOT NULL AUTO_INCREMENT COMMENT 'ID'",
            "spec_name"         => "varchar(300) NOT NULL COMMENT '专题名称'",
            "spec_status"       => "enum('" . $_str_status . "') NOT NULL COMMENT '状态'",
            "spec_content"      => "text NOT NULL COMMENT '专题内容'",
            "spec_attach_id"    => "int NOT NULL COMMENT '附件ID'",
        );

        $_num_mysql = $this->obj_db->create_table(BG_DB_TABLE . "spec", $_arr_specCreat, "spec_id", "专题");

        if ($_num_mysql > 0) {
            $_str_alert = "y180105"; //更新成功
        } else {
            $_str_alert = "x180105"; //更新成功
        }

        return array(
            "alert" => $_str_alert, //更新成功
        );
    }


    function mdl_column() {
        $_arr_colRows = $this->obj_db->show_columns(BG_DB_TABLE . "spec");

        foreach ($_arr_colRows as $_key=>$_value) {
            $_arr_col[] = $_value["Field"];
        }

        return $_arr_col;
    }


    function mdl_alert_table() {
        foreach ($this->specStatus as $_key=>$_value) {
            $_arr_status[] = $_key;
        }
        $_str_status = implode("','", $_arr_status);

        $_arr_col     = $this->mdl_column();
        $_arr_alert   = array();

        if (!in_array("spec_attach_id", $_arr_col)) {
            $_arr_alert["spec_attach_id"] = array("ADD", "int NOT NULL COMMENT '附件ID'");
        }

        if (in_array("spec_id", $_arr_col)) {
            $_arr_alert["spec_id"] = array("CHANGE", "int NOT NULL AUTO_INCREMENT COMMENT 'ID'", "spec_id");
        }

        if (in_array("spec_status", $_arr_col)) {
            $_arr_alert["spec_status"] = array("CHANGE", "enum('" . $_str_status . "') NOT NULL COMMENT '状态'", "spec_status");
        }

        if (in_array("spec_content", $_arr_col)) {
            $_arr_alert["spec_content"] = array("CHANGE", "text NOT NULL COMMENT '专题内容'", "spec_content");
        }

        $_str_alert = "y180111";

        if ($_arr_alert) {
            $_reselt = $this->obj_db->alert_table(BG_DB_TABLE . "spec", $_arr_alert);

            if ($_reselt) {
                $_str_alert = "y180106";
                $_arr_specData = array(
                    "spec_status" => $_arr_status[0],
                );
                $this->obj_db->update(BG_DB_TABLE . "spec", $_arr_specData, "LENGTH(spec_status) < 1"); //更新数据
            }
        }

        return array(
            "alert" => $_str_alert,
        );
    }


    /**
     * mdl_submit function.
     *
     * @access public
     * @param mixed $num_specId
     * @param mixed $str_specName
     * @param mixed $str_specType
     * @param mixed $str_specStatus
     * @return void
     */
    function mdl_submit() {

        $_arr_specData = array(
            "spec_name"         => $this->specSubmit["spec_name"],
            "spec_status"       => $this->specSubmit["spec_status"],
            "spec_content"      => $this->specSubmit["spec_content"],
            "spec_attach_id"    => $this->specSubmit["spec_attach_id"],
        );

        if ($this->specSubmit["spec_id"] < 1) {

            $_num_specId = $this->obj_db->insert(BG_DB_TABLE . "spec", $_arr_specData);

            if ($_num_specId > 0) { //数据库插入是否成功
                $_str_alert = "y180101";
            } else {
                return array(
                    "spec_id"   => $_num_specId,
                    "alert"     => "x180101",
                );
            }

        } else {
            $_num_specId = $this->specSubmit["spec_id"];
            $_num_mysql  = $this->obj_db->update(BG_DB_TABLE . "spec", $_arr_specData, "spec_id=" . $_num_specId);

            if ($_num_mysql > 0) {
                $_str_alert = "y180103";
            } else {
                return array(
                    "spec_id"   => $_num_specId,
                    "alert"     => "x180103",
                );
            }
        }

        return array(
            "spec_id"   => $_num_specId,
            "alert"     => $_str_alert,
        );
    }


    /**
     * mdl_read function.
     *
     * @access public
     * @param mixed $str_spec
     * @param string $str_readBy (default: "spec_id")
     * @param int $num_notThisId (default: 0)
     * @param int $num_parentId (default: 0)
     * @return void
     */
    function mdl_read($str_spec, $str_readBy = "spec_id", $num_notId = 0, $is_max = false) {
        $_arr_specSelect = array(
            "spec_id",
            "spec_name",
            "spec_status",
            "spec_content",
            "spec_attach_id",
        );

        if ($is_max) {
            if ($str_spec > 0) {
                $_str_sqlWhere = $str_readBy . "<" . $str_spec;
            } else {
                $_str_sqlWhere = "1=1";
            }
        } else {
            switch ($str_readBy) {
                case "spec_id":
                    $_str_sqlWhere = $str_readBy . "=" . $str_spec;
                break;
                default:
                    $_str_sqlWhere = $str_readBy . "='" . $str_spec . "'";
                break;
            }
        }

        if ($num_notId > 0) {
            $_str_sqlWhere .= " AND spec_id<>" . $num_notId;
        }

        $_arr_order = array(
            array("spec_id", "DESC"),
        );

        $_arr_specRows = $this->obj_db->select(BG_DB_TABLE . "spec",  $_arr_specSelect, $_str_sqlWhere, "", $_arr_order, 1, 0); //检查本地表是否存在记录

        if (isset($_arr_specRows[0])) {
            $_arr_specRow = $_arr_specRows[0];
        } else {
            return array(
                "alert" => "x180102", //不存在记录
            );
        }

        $_arr_specRow["spec_content"]   = stripslashes($_arr_specRow["spec_content"]);
        $_arr_specRow["urlRow"]         = $this->url_process($_arr_specRow);
        $_arr_specRow["alert"]          = "y180102";

        return $_arr_specRow;
    }


    function mdl_status($str_status) {

        $_str_specId = implode(",", $this->specIds["spec_ids"]);

        $_arr_specUpdate = array(
            "spec_status" => $str_status,
        );

        $_num_mysql = $this->obj_db->update(BG_DB_TABLE . "spec", $_arr_specUpdate, "spec_id IN (" . $_str_specId . ")"); //删除数据

        //如车影响行数小于0则返回错误
        if ($_num_mysql > 0) {
            $_str_alert = "y180103";
        } else {
            $_str_alert = "x180103";
        }

        return array(
            "alert" => $_str_alert,
        );
    }


    function mdl_primary() {
        $_arr_specData = array(
            "spec_attach_id"  => $this->specPrimary["spec_attach_id"],
        );

        //print_r($_arr_specData);

        $_num_specId = $this->specPrimary["spec_id"];
        $_num_mysql     = $this->obj_db->update(BG_DB_TABLE . "spec", $_arr_specData, "spec_id=" . $_num_specId); //更新数据

        if ($_num_mysql > 0) {
            $_str_alert  = "y180103";
        } else {
            $_str_alert  = "x180103";
        }

        /*print_r($_arr_userRow);
        exit;*/

        return array(
            "spec_id"   => $_num_specId,
            "alert"     => $_str_alert,
        );
    }


    /**
     * mdl_list function.
     *
     * @access public
     * @param string $str_status (default: "")
     * @param string $str_type (default: "")
     * @param int $num_parentId (default: 0)
     * @return void
     */
    function mdl_list($num_no, $num_except = 0, $arr_search = array()) {
        $_arr_specSelect = array(
            "spec_id",
            "spec_name",
            "spec_status",
            "spec_attach_id",
        );

        $_str_sqlWhere = $this->sql_process($arr_search);

        if (isset($arr_search["article_id"]) && $arr_search["article_id"] > 0) {
            $_view_name = "spec_view";
        } else {
            $_view_name = "spec";
        }

        //print_r($_str_sqlWhere);

        $_arr_order = array(
            array("spec_id", "DESC"),
        );

        $_arr_specRows = $this->obj_db->select(BG_DB_TABLE . $_view_name,  $_arr_specSelect, $_str_sqlWhere, "", $_arr_order, $num_no, $num_except);

        foreach ($_arr_specRows as $_key=>$_value) {
            $_arr_specRows[$_key]["urlRow"] = $this->url_process($_value);
        }

        return $_arr_specRows;
    }


    function mdl_count($arr_search = array()) {

        $_str_sqlWhere = $this->sql_process($arr_search);

        $_num_specCount = $this->obj_db->count(BG_DB_TABLE . "spec", $_str_sqlWhere); //查询数据

        /*print_r($_arr_userRow);
        exit;*/

        return $_num_specCount;
    }


    /**
     * mdl_del function.
     *
     * @access public
     * @param mixed $this->specIds["spec_ids"]
     * @return void
     */
    function mdl_del() {
        $_str_specIds = implode(",", $this->specIds["spec_ids"]);

        $_num_mysql = $this->obj_db->delete(BG_DB_TABLE . "spec",  "spec_id IN (" . $_str_specIds . ")"); //删除数据

        //如车影响行数小于0则返回错误
        if ($_num_mysql > 0) {
            $_str_alert = "y180104";
        } else {
            $_str_alert = "x180104";
        }

        return array(
            "alert" => $_str_alert,
        ); //成功
    }


    function input_submit() {
        if (!fn_token("chk")) { //令牌
            return array(
                "alert" => "x030206",
            );
        }

        $this->specSubmit["spec_id"] = fn_getSafe(fn_post("spec_id"), "int", 0);

        if ($this->specSubmit["spec_id"] > 0) {
            $_arr_specRow = $this->mdl_read($this->specSubmit["spec_id"]);
            if ($_arr_specRow["alert"] != "y180102") {
                return $_arr_specRow;
            }
        }

        $_arr_specName = validateStr(fn_post("spec_name"), 1, 300);
        switch ($_arr_specName["status"]) {
            case "too_short":
                return array(
                    "alert" => "x180201",
                );
            break;

            case "too_long":
                return array(
                    "alert" => "x180202",
                );
            break;

            case "ok":
                $this->specSubmit["spec_name"] = $_arr_specName["str"];
            break;
        }

        $_arr_specStatus = validateStr(fn_post("spec_status"), 1, 0);
        switch ($_arr_specStatus["status"]) {
            case "too_short":
                return array(
                    "alert" => "x180201",
                );
            break;

            case "ok":
                $this->specSubmit["spec_status"] = $_arr_specStatus["str"];
            break;
        }

        $this->specSubmit["spec_content"] = fn_post("spec_content");

        $_arr_attachIds = fn_getAttach($this->specSubmit["spec_content"]);
        if ($_arr_attachIds) {
            $this->specSubmit["spec_attach_id"] = $_arr_attachIds[0];
        } else {
            $this->specSubmit["spec_attach_id"] = 0;
        }

        if (!$this->is_magic) {
            $this->specSubmit["spec_content"]   = addslashes($this->specSubmit["spec_content"]);
        }

        $this->specSubmit["alert"] = "ok";

        return $this->specSubmit;
    }


    function input_primary() {
        if (!fn_token("chk")) { //令牌
            return array(
                "alert" => "x030206",
            );
        }

        $_arr_specId = validateStr(fn_post("spec_id"), 1, 0);
        switch ($_arr_specId["status"]) {
            case "too_short":
                return array(
                    "alert" => "x180204",
                );
            break;

            case "ok":
                $this->specPrimary["spec_id"] = $_arr_specId["str"];
            break;
        }

        $_arr_specRow  = $this->mdl_read($this->specPrimary["spec_id"]);
        if ($_arr_specRow["alert"] != "y180102") {
            return $_arr_specRow;
        }

        $_arr_attachId = validateStr(fn_post("attach_id"), 1, 0);
        switch ($_arr_attachId["status"]) {
            case "too_short":
                return array(
                    "alert" => "x180206",
                );
            break;

            case "ok":
                $this->specPrimary["spec_attach_id"] = $_arr_attachId["str"];
            break;
        }

        $this->specPrimary["alert"]  = "ok";

        return $this->specPrimary;
    }


    /**
     * input_ids function.
     *
     * @access public
     * @return void
     */
    function input_ids() {
        if (!fn_token("chk")) { //令牌
            return array(
                "alert" => "x030206",
            );
        }

        $_arr_specIds = fn_post("spec_ids");

        if ($_arr_specIds) {
            foreach ($_arr_specIds as $_key=>$_value) {
                $_arr_specIds[$_key] = fn_getSafe($_value, "int", 0);
            }
            $_str_alert = "ok";
        } else {
            $_str_alert = "x030202";
        }

        $this->specIds = array(
            "alert"     => $_str_alert,
            "spec_ids"  => $_arr_specIds
        );

        return $this->specIds;
    }


    function url_process_global() {
        $_str_tpl           = "";
        $_str_specPath      = "";
        $_str_specPathShort = "";
        $_str_specUrl       = "";
        $_str_specUrlMore   = "";
        $_str_pageAttach    = "";
        $_str_pageExt       = "";

        switch (BG_VISIT_TYPE) {
            case "static":
                $_str_specPath      = BG_PATH_ROOT . "focus/";
                $_str_specPathShort = "/focus/";
                $_str_specUrl       = $this->attachPre . "focus/";
                $_str_specUrlMore   = $this->attachPre . "spec/";
                $_str_pageAttach    = "page-";
                $_str_pageExt       = "." . BG_VISIT_FILE;
            break;

            case "pstatic":
                $_str_specUrl       = $this->attachPre . "spec/";
                $_str_pageAttach    = "page-";
            break;

            default:
                $_str_specUrl       = $this->attachPre . "index.php?mod=spec&act_get=list";
                $_str_pageAttach    = "&page=";
            break;
        }

        if (defined("BG_SITE_TPL")) {
            $_str_tpl = BG_SITE_TPL;
        } else {
            $_str_tpl = "default";
        }

        return array(
            "spec_tpl"          => $_str_tpl,
            "spec_path"         => $_str_specPath,
            "spec_pathShort"    => $_str_specPathShort,
            "spec_url"          => $_str_specUrl,
            "spec_urlMore"      => $_str_specUrlMore,
            "page_attach"       => $_str_pageAttach,
            "page_ext"          => $_str_pageExt,
        );
    }


    private function url_process($_arr_specRow) {
        $_str_specPath      = "";
        $_str_specPathShort = "";
        $_str_specUrl       = "";
        $_str_specUrlMore   = "";
        $_str_pageAttach    = "";
        $_str_pageExt       = "";

        switch (BG_VISIT_TYPE) {
            case "static":
                $_str_specPath      = BG_PATH_ROOT . "focus/" . $_arr_specRow["spec_id"] . "/";
                $_str_specPathShort = "/focus/" . $_arr_specRow["spec_id"] . "/";
                $_str_specUrl       = $this->attachPre . "focus/" . $_arr_specRow["spec_id"] . "/";
                $_str_specUrlMore   = $this->attachPre . "spec/id-" . $_arr_specRow["spec_id"] . "/";
                $_str_pageAttach    = "page-";
                $_str_pageExt       = "." . BG_VISIT_FILE;
            break;

            case "pstatic":
                $_str_specUrl       = $this->attachPre . "spec/id-" . $_arr_specRow["spec_id"] . "/";
                $_str_pageAttach    = "page-";
            break;

            default:
                $_str_specUrl       = $this->attachPre . "index.php?mod=spec&act_get=show&spec_id=" . $_arr_specRow["spec_id"];
                $_str_pageAttach    = "&page=";
            break;
        }

        return array(
            "spec_path"         => $_str_specPath,
            "spec_pathShort"    => $_str_specPathShort,
            "spec_url"          => $_str_specUrl,
            "spec_urlMore"      => $_str_specUrlMore,
            "page_attach"       => $_str_pageAttach,
            "page_ext"          => $_str_pageExt,
        );
    }


    private function sql_process($arr_search = array()) {
        $_str_sqlWhere = "1=1";

        if (isset($arr_search["key"]) && !fn_isEmpty($arr_search["key"])) {
            $_str_sqlWhere .= " AND spec_name LIKE '%" . $arr_search["key"] . "%'";
        }

        if (isset($arr_search["status"]) && !fn_isEmpty($arr_search["status"])) {
            $_str_sqlWhere .= " AND spec_status='" . $arr_search["status"] . "'";
        }

        if (isset($arr_search["spec_ids"]) && $arr_search["spec_ids"]) {
            $_str_specIds    = implode(",", $arr_search["spec_ids"]);
            $_str_sqlWhere  .= " AND spec_id IN (" . $_str_specIds . ")";
        }

        if (isset($arr_search["article_id"]) && $arr_search["article_id"] > 0) {
            $_str_sqlWhere .= " AND belong_article_id=" . $arr_search["article_id"];
        }

        return $_str_sqlWhere;
    }
}
