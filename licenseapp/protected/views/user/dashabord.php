<div class="image-container set-full-height">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <!-- Wizard container -->
                <div class="wizard-container">
                    <div class="card wizard-card" data-color="red" id="wizard">
                        <form action="" method="">
                            <div class="wizard-header">
                                <h3 class="wizard-title" style="position: relative">
                                    <?php echo Yii::t('ui', 'portal_title'); ?>
                                    <div class="pull-right mr-3" style=" position: absolute;   right: 0;    top: -16px;   right: 18px;"> 
                                        <a href="<?php echo $this->createUrl('user/logout'); ?>" class="btn btn-dark mr-3"> <span class="material-icons">power_settings_new</span> </a></div>
                                </h3>
                            </div>
                            <div class="wizard-navigation">
                                <ul class="nav nav-pills">
                                    <li style="width: 33.3333%; display: none;"><a href="#details" data-toggle="tab" aria-expanded="false">  </a></li>
                                    <li style="width: 50%;" class="active"><a class="active" href="#captain" data-toggle="tab" aria-expanded="true"> </a></li>
                                    <li style="width: 33%;"><a href="<?php echo $this->createUrl('user/trackLoginUser'); ?>"> Concurrent Users List  </a></li>
                                </ul>
                                <div class="moving-tab" style="width: 240px;transform: translate3d(315px, 0px, 0px);transition: all 0.5s cubic-bezier(0.29, 1.42, 0.79, 1) 0s;"><?php echo Yii::t('ui', 'dashboard'); ?></div>

                                <div class="tab-content">
                                    <div class="tab-pane" id="details">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <h4 class="info-text"> Let's start with the basic details.</h4>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="material-icons">account_box</i>
                                                    </span>
                                                    <div class="form-group label-floating">
                                                        <label class="control-label">Your User Name</label>
                                                        <input name="name" type="text" class="form-control">
                                                    </div>
                                                </div>
                                                <div class="input-group">
                                                    <span class="input-group-addon">
                                                        <i class="material-icons">lock_outline</i>
                                                    </span>
                                                    <div class="form-group label-floating">
                                                        <label class="control-label">Your Password</label>
                                                        <input name="name2" type="password" class="form-control">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="tab-pane show" id="captain">
                                        <h4 class="info-text"> <?php echo Yii::t('ui', 'welcome') . ' ' . $dashboard['username']; ?></h4>
                                        <div class="row">
                                            <div class="col-sm-10 col-sm-offset-1">
                                                <div class="col-sm-6">
                                                    <div class="choice">
                                                        <input type="radio" name="job" value="Design">
                                                        <div class="icon">
                                                            <i class="material-icons">verified</i>
                                                        </div>
                                                        <h6><?php echo Yii::t('ui', 'license_no'); ?> : <span class="amt"> <?php echo $dashboard['license']; ?> </span> </h6>
                                                    </div>
                                                </div>
                                                <div class="col-sm-6">
                                                    <div class="choice">
                                                        <input type="radio" name="job" value="Code">
                                                        <div class="icon">
                                                            <i class="material-icons">language</i>
                                                        </div>
                                                        <h6> <?php echo Yii::t('ui', 'browser_details'); ?>
                                                            <span class="amt"><?php echo Yii::t('ui', 'device_type') . ' ' . $dashboard['devtype']; ?></span>
                                                            <span class="amt"><?php echo Yii::t('ui', 'browser') . ' ' . $dashboard['sysbrowser']; ?></span>
                                                            <span class="amt"><?php echo Yii::t('ui', 'sys_os') . ' ' . $dashboard['sysos']; ?></span>
                                                            <span class="amt"><?php echo Yii::t('ui', 'ip') . ' ' . $dashboard['sysip']; ?> </span>
                                                        </h6>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div> 
</div>
</div>

