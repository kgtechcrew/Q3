<?php
$form = $this->beginWidget('CActiveForm', array(
    'id'                   => 'login_form',
    'enableAjaxValidation' => false,
        ));
?>
<div class="image-container set-full-height">
    <div class="container">
        <div class="row">
            <div class="col-sm-8 col-sm-offset-2">
                <div class="wizard-container">
                    <div class="card wizard-card" data-color="red" id="wizard">
                        <div class="wizard-header">
                            <h3 class="wizard-title">
                                <?php echo Yii::t('ui', 'portal_title'); ?>
                            </h3>
                        </div>
                        <div class="wizard-navigation">
                            <ul class="nav nav-pills">
                                <li class="" style="width: 33.3333%;"><a href="#details" data-toggle="tab" aria-expanded="false">  </a></li>
                                <li style="width: 33.3333%;" class="active"><a class="active" href="#captain" data-toggle="tab" aria-expanded="true"><?php echo Yii::t('ui', 'login'); ?></a></li>
                                <li style="width: 33.3333%;"><a href="#description" data-toggle="tab">  </a></li>
                            </ul>
                            <div class="moving-tab" style="width: 250px; transform: translate3d(250px, 0px, 0px); transition: all 0.5s cubic-bezier(0.29, 1.42, 0.79, 1) 0s;"><?php echo Yii::t('ui', 'login'); ?></div></div>
                        <div class="tab-content">
                            <div class="tab-pane show" id="details">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <h4 class="info-text"><?php echo Yii::t('ui', 'login_basic_info'); ?></h4>
                                    </div>
                                    <div class="col-sm-12">
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="material-icons">account_box</i>
                                            </span>
                                            <div class="form-group label-floating">
                                                <label class="control-label"><?php echo Yii::t('ui', 'username'); ?></label>
                                                <?php echo CHtml::textField('loginForm[username]', '', array('class' => 'form-control', 'id' => 'username', 'onCopy' => 'return false;', 'onDrop' => 'return false', 'autocomplete' => 'off')); ?>
                                                <span class="errorMessage" id="username_error"></span>
                                            </div>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-addon">
                                                <i class="material-icons">lock_outline</i>
                                            </span>
                                            <div class="form-group label-floating">
                                                <label class="control-label"><?php echo Yii::t('ui', 'password'); ?></label>
                                                <?php echo CHtml::passwordField('loginForm[password]', '', array('class' => 'form-control', 'id' => 'password', 'onCopy' => 'return false;', 'onDrop' => 'return false', 'autocomplete' => 'off')); ?>
                                                <span class="errorMessage" id="password_error"></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wizard-footer">
                            <div class="text-center">
                                <button type="button" class="btn btn-next btn-fill btn-success btn-wd" onclick="loginValdation();"> 
                                    <?php echo Yii::t('ui', 'signin'); ?>
                                    <span class="material-icons">login</span>
                                </button>
                            </div>
                            <div class="clearfix"></div>
                        </div>

                    </div>
                </div> 
            </div>
        </div>
    </div>
</div>


<!-- Login Validation Popup Message -->
<div class="modal fade" id="exampleModalCenter" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLongTitle"><?php echo Yii::t('ui', 'popup_title'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="login_error_message"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal"><?php echo Yii::t('ui', 'close'); ?></button>
            </div>
        </div>
    </div>
</div>
<?php $this->endWidget(); ?>