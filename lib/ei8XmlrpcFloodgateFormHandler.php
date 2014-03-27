<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yipeecaiey
 * Date: 10/18/13
 * Time: 2:35 PM
 * To change this template use File | Settings | File Templates.
 */
class ei8XmlrpcFloodgateFormHandler
{
    public $form_action;
    public $action;
    public $form_method;
    public $body;
    public $fields;
    public $message;
    public $status;

    public $var_pre = 'ei8_floodgate_form_var_';

    public $line_open;
    public $line_middle;
    public $line_close;

    public function __construct($action='',$method='') {
        if($action!='') $this->form_action = $action;
        if($method!='') $this->form_action = $method;

        $this->set_fields();

        $this->line_open = "<div class='ei8-form-line'>";
        $this->line_middle = "";
        $this->line_close = "</div>";

        //echo "<p>REQUEST:<pre>"; print_r($_REQUEST); echo "</pre></p>"; exit;

        if($_REQUEST[$this->var_pre . 'action']!='') {
            //echo "<p>We've got an action!</p>"; exit;
            $this->status = 'action';
            $this->action = $_REQUEST[$this->var_pre . 'action'];
            $this->validate();
            $this->process();
        }
    }

    public function process() {
        //placeholder...we have no idea what each form needs to do to process itself
    }

    public function validate() {
        foreach($this->fields as $field) {
            if(!isset($_REQUEST[$field->var_form])) continue; //skips buttons
            $field->set_value($_REQUEST[$field->var_form]);
            $field->validate();
        }
        if ($this->errors_exist()) $this->status = 'error';
    }

    public function errors_exist() {
        $errorsExist = false;
        $errorMsg = '';
        foreach($this->fields as $field) if($field->error) {
            $errorsExist = true;
            $errorMsg .= $field->error_message;
        }
        if($errorsExist) {
            $this->message = "Errors exist. Please try again.".$errorMsg;
        }
        return $errorsExist;
    }

    public function add_field($field) {
        //make sure this is a valid field
        if(gettype($field)=='object' && strstr(get_class($field),'ei8XmlrpcFloodgateFormField')) $this->fields[$field->var] = $field;
        else $this->fields[] = new ei8XmlrpcFloodgateFormField('unknown');
    }

    public function set_fields() {
        //placeholder for child classes
        $this->fields = array();
    }

    public function render() {
        $showAction = (!empty($this->action)) ? sprintf('action="%s"',$this->action) : '' ;
        $showMethod = (!empty($this->method)) ? sprintf('method="%s"',$this->method) : '' ;
        $showBody   = $this->render_body();
        $showMsg    = $this->render_message();
        $html =<<<EOT
        $showMsg
        <form method="post" $showAction $showMethod>
            $showBody
        </form>
EOT;
        return $html;
    }

    public function render_body() {
        if(!empty($this->body)) return $this->body;
        //echo "<p>fields:<pre>"; print_r($this->fields); echo "</pre></p>"; exit;
        foreach($this->fields as $field) {
            $this->body .= $this->render_line($field);
        }
        return $this->body;
    }

    public function render_line($field) {
        return $this->line_open . $field->render_label() . $this->line_middle . $field->render_field() . $this->line_close;
    }

    public function render_message() {
        $html = (!empty($this->message)) ? sprintf('<div class="form-%s"><div class="form-message">%s</div></div>',$this->status,$this->message) : '' ;
        return $html;
    }

    public function redirect($url) {
        return ei8XmlrpcFloodgatePage::redirect($url);
    }

    public function set_form_lines($open,$close,$middle=''){
        $this->line_open = $open;
        $this->line_middle = $middle;
        $this->line_close = $close;
    }

}

class ei8XmlrpcFloodgateFormLogin extends ei8XmlrpcFloodgateFormHandler
{
    public $session;
    public $src;

    public function set_fields() {
        $this->fields = array();
        $this->add_field(new ei8XmlrpcFloodgateFormFieldPassword('password','',array('label'=>'Please Enter Your Password')));
        $this->fields['password']->label = 'Please Enter Your Password';
        $this->fields['password']->required = true;
        $this->add_field(new ei8XmlrpcFloodgateFormFieldHidden('action','login'));
        $this->add_field(new ei8XmlrpcFloodgateFormFieldSubmit('submit','Submit'));
    }

    public function process() {
        //echo "<p>REQUEST:<pre>"; print_r($_REQUEST); echo "</pre></p>"; exit;
        if($this->status=='action' && $this->action=='login') {
            $this->session = new ei8XmlrpcFloodgateSession();
            //$session->validate();
            $this->session->try_login($this->fields['password']->value);
            if($this->session->is_valid()) {
                //redirect
                //echo '<p>YOU ARE LOGGED IN!<pre>'; print_r($this->session); echo '</pre></p>';
                //$this->redirect($this->floodgateUrl);
                $this->status='success';
            } else {
                //show fancy error message?
                $this->status='error';
                //$this->message = 'Please try again';
                $this->fields['password']->label = 'Please try again';
                //$title = "<span class='errormessage'>Please try again</span>";
            }
        }
    }

    public function render() {
        $html = parent::render();
        $passwordField = ei8XmlrpcFloodgateFormField::prep_var_name('password');
        $js =<<<EOT
            <script type="text/javascript" src="{$this->src}/lib/js/jquery-1.3.2.min.js"></script>
            <script type="text/javascript">
                $(document).ready(function() {
                    $('#$passwordField').focus();
                });
            </script>
EOT;

        return $html.$js;
    }
}

/*class ei8XmlrpcFloodgateFormTextSubmit extends ei8XmlrpcFloodgateFormHandler
{
    public $session;

    public function set_fields() {
        $this->fields = array();
        $this->add_field(new ei8XmlrpcFloodgateFormFieldPassword('password','',array('label'=>'Please Enter Your Password')));
        $this->fields['password']->label = 'Please Enter Your Password';
        $this->fields['password']->required = true;
        $this->add_field(new ei8XmlrpcFloodgateFormFieldHidden('action','login'));
        $this->add_field(new ei8XmlrpcFloodgateFormFieldSubmit('submit','Submit'));
    }

    public function process() {
        //echo "<p>REQUEST:<pre>"; print_r($_REQUEST); echo "</pre></p>"; exit;
        if($this->status=='action' && $this->action=='login') {
            $this->session = new ei8XmlrpcFloodgateSession();
            //$session->validate();
            $this->session->try_login($this->fields['password']->value);
            if($this->session->is_valid()) {
                //redirect
                //echo '<p>YOU ARE LOGGED IN!<pre>'; print_r($this->session); echo '</pre></p>';
                //$this->redirect($this->floodgateUrl);
                $this->status='success';
            } else {
                //show fancy error message?
                $this->status='error';
                //$this->message = 'Please try again';
                $this->fields['password']->label = 'Please try again';
                //$title = "<span class='errormessage'>Please try again</span>";
            }
        }

    }
}*/

class ei8XmlrpcFloodgateFormContentSubmit extends ei8XmlrpcFloodgateFormHandler
{
    public $src;
    public $type;
    public $guid;

    public function __construct($type,$guid,$src) {
        parent::__construct();
        $this->type = $type;
        $this->guid = $guid;
        $this->src  = $src;
    }

    public function render() {
        $api        = new ei8XmlrpcFloodgateAPI();
        $submitURL  = $api->buildUploadURL($this->type,$this->guid);
        $aName      = 'ei8fgcontentsubmit'.$this->type;
        $returnURL  = $this->src."?submitted=".$aName;
        //$selectFile = ($this->type=='text') ? 'Select file to upload:' : '' ;

        if (($_REQUEST['submitted']==$aName)) {
            $showConf  = ($_REQUEST['result']=='success') ? ei8_xmlrpc_conf_message() : ei8_xmlrpc_conf_message(false, 'We encountered an error while processing your submission', $_REQUEST['msg']);
        } else $showConf = "";


        $htmlText =<<<EOT
    $showConf
    <form action="$submitURL" enctype="multipart/form-data" method="post">
    <div class="ei8-form-wrapper">
        <div class="ei8-form-line">
            <div class="ei8-form-label">Title:</div>
            <div class="ei8-form-field"><input name="uptitle" size="40" type="text" /></div>
        </div>
        <div class="ei8-form-line">
            <div class="ei8-form-label">Content:</div>
            <div class="ei8-form-field"><textarea class="ei8-textarea-simple-submit" name="updesc"></textarea></div>
        </div>
        <div class="ei8-form-line">
            <div class="ei8-form-line-double">Attach file: (optional) <input name="upfile" type="file" id="upfile" /></div>
        </div>
        <div class="ei8-form-line-spacer"></div>
        <div class="ei8-form-line">
            <div class="ei8-form-label"></div>
            <div class="ei8-form-field">
                <input type="hidden" name="returnURL" value="$returnURL">
                <input name="Submit" type="submit" value="Submit" class="ei8-form-button" />
            </div>
        </div>
    </div>
    </form>
EOT;


        $htmlImage =<<<EOT
    $showConf
    <form action="$submitURL" enctype="multipart/form-data" method="post">
    <div class="ei8-form-wrapper">
        <div class="ei8-form-line">
            <div class="ei8-form-line-double"><input name="upfile" type="file" id="upfile" /></div>
        </div>
        <div class="ei8-form-line">
            <div class="ei8-form-label">Title:</div>
            <div class="ei8-form-field"><input name="uptitle" size="40" type="text"  /></div>
        </div>
        <div class="ei8-form-line">
            <div class="ei8-form-label">Content:</div>
            <div class="ei8-form-field"><textarea class="ei8-textarea-simple-submit" name="updesc"></textarea></div>
        </div>
        <div class="ei8-form-line-spacer"></div>
        <div class="ei8-form-line">
            <div class="ei8-form-label"></div>
            <div class="ei8-form-field">
                <input type="hidden" name="returnURL" value="$returnURL">
                <input name="Submit" type="submit" value="Submit" class="ei8-form-button" />
            </div>
        </div>
    </div>
    </form>
EOT;
        return ($this->type=='text') ? $htmlText : $htmlImage ;
    }
}

class ei8XmlrpcFloodgateFormUploader extends ei8XmlrpcFloodgateFormHandler
{
    public $src;
    public $type;
    public $guid;

    public function __construct($type,$guid,$src) {
        parent::__construct();
        $this->type = $type;
        $this->guid = $guid;
        $this->src  = $src;

        //temp...until the API uploader is functioning
        $parts = explode("&",$this->guid);
        foreach($parts as $part) {
            list($type,$guid) = explode("=",$part);
            $name = $type.'fguid';
            $this->$name = $guid;
        }
    }

    public function render() {
        //$uploadifyJS = ei8_plugins_url('uploadify.js');
        $xsid = json_encode(session_id());
        //$guids = ($this->type=='audio') ? "{$this->guid}/default" : "default/{$this->guid}";
        $html =<<<EOT
        <div class="uploader">
            <div class="content">
                <div class="ei8-form-wrapper">
                    <div id="eventsmessage"></div>
                    <div class="ei8-form-line">
                        <div class="ei8-form-label">Title:</div>
                        <div class="ei8-form-field"><input name="uptitle" type="text" id="uptitle" /></div>
                    </div>
                    <div class="ei8-form-line">
                        <div class="ei8-form-label">Content:</div>
                        <div class="ei8-form-field"><textarea class="ei8-textarea-updesc" name="updesc" id="updesc"></textarea></div>
                    </div>
                    <div class="ei8-form-line">
                        <div class="ei8-form-line-double">
                            <div id="mediauploader">Choose File</div>
                        </div>
                    </div>
                    <div class="ei8-form-line">
                        <div class="ei8-form-line-double">
                            <div id="startUpload" class='ei8-form-button'>Submit</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            $(document).ready(function()
            {
                $("#startUpload").hide();
                var uploadObj = $("#mediauploader").uploadFile({
                    url:"http://www.ei8t.com/api/upload/{$this->type}/{$this->guid}/",
                    //allowedTypes:"png,gif,jpg,jpeg",
                    autoSubmit:false,
                    fileName:"myfile",
                    formData: {"uptitle":"File Upload","updesc":""},
                    showStatusAfterSuccess:false,
                    multiple:false,
                    showFileCounter:false,
                    showQueueDiv:false,
                    uploadButtonClass:"ei8-form-button",
                    dragDropStr:"<div class='dragdroptext'> Or Drag & Drop Files Here</div>",
                    dynamicFormData: function()
                    {
                        var data ={ uptitle:$('#uptitle').val(), updesc:$('#updesc').val() }
                        return data;
                    },
                    onSelect:function(files)
                    {
                        $("#startUpload").show();
                        return files;
                    },
                    onSubmit:function(files)
                    {
                    	$("#startUpload").hide();
                    },
                    onSuccess:function(files,data,xhr)
                    {
                        response = JSON.parse(data);

                        status = (response.result == 'success');
                        title = '';
                        msg = response.msg;

                    	$("#eventsmessage").html(floodgate_response(status,title,msg));
                    	$('#uptitle').val("");
                    	$('#updesc').val("");
                    	floodgate_response_hide();
                    	$("#startUpload").hide();

                    },
                    onError: function(files,status,errMsg)
                    {
                        $("#eventsmessage").html($("#eventsmessage").html()+"<br/>Error for: "+JSON.stringify(files));
                    }
                });
                $("#startUpload").click(function()
                {
                    //var uptitle = $('#uptitle').val();
                    //var updesc = $('#updesc').val();

                    //uploadObj.formData = {"uptitle":uptitle,"updesc":updesc};
                    uploadObj.startUpload();
                });
            });
        </script>
EOT;
        return $html;
    }
}

class ei8XmlrpcFloodgateFormFG extends ei8XmlrpcFloodgateFormHandler
{
    public function __construct($action='',$method='') {
        $this->submitButton = 'Submit';
        if($action!='') $this->action = $action;
        if($method!='') $this->action = $method;
    }

    public function process() {
    }

    public function build_table($fields) {
        $table = '<table class="form-table">';
        foreach($fields as $var=>$arr) {
            $type   = array_shift($arr);
            $title = ($type=='hidden') ? '' : array_shift($arr) ;
            $showField = $this->field_maker($type,$this->prep_var_name($var),$arr);
            $table .= ($type=='hidden') ? $showField : sprintf('<tr valign="top"><th scope="row">%s: </th><td>%s</td></tr>', $title, $showField);
        }
        $table .= '</table>';
        return $table;
    }

    public function prep_var_name($var) {
        return 'ei8_floodgate_form_var_'.$var;
    }

    public function field_maker($type,$var,$args) {
        //list($type,$val,$extra1,$extra2) = $args;
        $method = 'make_field_'.$type;
        //if(empty($extra2)) array_pop($args);
        //if(empty($extra1)) array_pop($args);
        switch($type) {
            case 'hidden':
            case 'password':
            case 'boolean':
                $field = $this->$method($var, $args[0]);
                break;
            case 'text':
                $field = $this->$method($var, $args[0], $args[1]);
                break;
            case 'textarea':
                $field = $this->$method($var, $args[0], $args[1], $args[2]);
                break;
            default:
                $field = '<p>SCRIPT ERROR ($var)</p>';
                break;
        }
        //$field = '<p>numArgs('.count($args).')</p>'.$field;
        return $field;
    }

    public function render() {
        $showAction = (!empty($this->action)) ? sprintf('action="%s"',$this->action) : '' ;
        $showMethod = (!empty($this->method)) ? sprintf('method="%s"',$this->method) : '' ;
        $showSubmit = $this->make_field_submit();
        $html =<<<EOT
        <form method="post" $showAction $showMethod>
            $this->body
            $showSubmit
        </form>
EOT;
        return $html;
    }
}
