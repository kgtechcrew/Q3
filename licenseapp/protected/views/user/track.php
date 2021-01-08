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
                                        <a href="<?php echo $this->createUrl('user/logout'); ?>"  class="btn btn-dark mr-3"> <span class="material-icons">power_settings_new</span> </a></div>
                                </h3>
                            </div>
                            <div class="wizard-navigation">
                                <ul class="nav nav-pills">
                                    <li style="width: 14%;"><a href="#details" data-toggle="tab" aria-expanded="false">  </a></li>
                                    <li style="width: 38%;" class="active"><a class="active" href="<?php echo $this->createUrl('user/dashboard'); ?>" aria-expanded="true"> Dashboard </a></li>
                                    <li style="width: 33%;"><a href="<?php echo $this->createUrl('user/trackLoginUser'); ?>"> Login User Tracking System  </a></li>
                                </ul>
                                <div class="moving-tab" style="width: 240px;transform: translate3d(636px, 0px, 0px);transition: all 0.5s cubic-bezier(0.29, 1.42, 0.79, 1) 0s;">Concurrent Users List</div>
                                <div class="tab-content">
                                    <div class="tab-pane show" id="description">
                                        <?php
                                        $active_user_list  = isset($login_user['loginusers']) ? $login_user['loginusers'] : array();
                                        $failure_user_list = isset($login_user['failedusers']) ? $login_user['failedusers'] : array();
                                        ?>
                                        <div class="row">
                                            <h3 class="info-text"> Active Users List </h3>
                                            <div class="container-fluid">
                                                <table class="table table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th scope="col">User Name</th>
                                                            <th scope="col">User Email</th>
                                                            <th scope="col">License Id</th>
                                                            <th scope="col">Login DateTime</th>
                                                            <th scope="col">IP</th>
                                                            <th scope="col">Browser</th>
                                                            <th scope="col">Operating System</th>
                                                            <th scope="col">Device Type</th>
                                                            <th scope="col">Number Of Devices</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        foreach ($active_user_list as $key => $value)
                                                        {
                                                            ?>
                                                            <tr>
                                                                <td><?php echo $value['User Name']; ?></td>
                                                                <td><?php echo $value['User Email']; ?></td>
                                                                <td><?php echo $value['LicenseId']; ?></td>
                                                                <td><?php echo $value['Login DateTime']; ?></td>
                                                                <td><?php echo $value['IP']; ?></td>
                                                                <td><?php echo $value['Browser']; ?></td>
                                                                <td><?php echo $value['Operating System']; ?></td>
                                                                <td><?php echo $value['Device Type']; ?></td>
                                                                <td><?php echo $value['Number Of Devices']; ?></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <h3 class="info-text"> Login Attempted User's List </h3>
                                            <div class="container-fluid">
                                                <table class="table table-bordered">
                                                    <thead class="thead-light">
                                                        <tr>
                                                            <th scope="col">User Name</th>
                                                            <th scope="col">User Email</th>
                                                            <th scope="col">License Id</th>
                                                            <th scope="col">Login DateTime</th>
                                                            <th scope="col">IP</th>
                                                            <th scope="col">Browser</th>
                                                            <th scope="col">Operating System</th>
                                                            <th scope="col">Device Type</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        foreach ($failure_user_list as $key => $value)
                                                        {
                                                            ?>
                                                            <tr>
                                                                <td><?php echo $value['User Name']; ?></td>
                                                                <td><?php echo $value['User Email']; ?></td>
                                                                <td><?php echo $value['LicenseId']; ?></td>
                                                                <td><?php echo $value['Login DateTime']; ?></td>
                                                                <td><?php echo $value['IP']; ?></td>
                                                                <td><?php echo $value['Browser']; ?></td>
                                                                <td><?php echo $value['Operating System']; ?></td>
                                                                <td><?php echo $value['Device Type']; ?></td>
                                                            </tr>
                                                            <?php
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
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