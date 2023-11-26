<?php $__env->startSection('header'); ?>
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/material-design/material-design.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/fonts/brand-icons/brand-icons.min.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/jquery-wizard/jquery-wizard.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/formvalidation/formValidation.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/global/vendor/select2/select2.css')); ?>">
    <link rel="stylesheet" href="<?php echo e(asset('assets/remark/examples/css/forms/advanced.css')); ?>">
<?php $__env->stopSection(); ?>
<?php $__env->startSection('body'); ?>
    <div class="page-content vertical-align-middle">
        <?php if(count($errors) > 0): ?>
            <div class="alert alert-danger bg-danger" style="width: 400px;">
                <p>Please fix the following issues to continue</p>
                <ul class="error">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
        <?php if(Session::has('error')): ?>
            <div class="alert alert-error bg-danger" style="width: 400px;">
                <?php echo e(Session::get('error')); ?>

            </div>
        <?php endif; ?>
        <?php if(Session::has('success')): ?>
            <div class="alert bg-green bg-success" style="width: 400px;">
                <?php echo e(Session::get('success')); ?>

            </div>
        <?php endif; ?>
        <div class="panel">
            <div class="panel-body">
                <div class="brand">
                    <img class="brand-img" src="<?php echo e(asset('assets/icons/Logo_RotoEye_Version_160 cross 160_PNG_1.0.png')); ?>"
                        alt="..." style="width: 40%;height: auto;">
                </div>
                <form action="<?php echo e(URL::to('login')); ?>" method="post" id="loginForm" name="loginForm">

                    <div class="form-group form-material floating" data-plugin="formMaterial">
                        <input type="text" class="form-control" name="username" value="<?php echo e(old('username')); ?>" autofocus
                            required="required" id="userName" />
                        <label class="floating-label">Employee ID</label>
                    </div>
                    <div class="form-group form-material floating" data-plugin="formMaterial">
                        <input type="password" class="form-control" name="password" required="required" />
                        <label class="floating-label">Password</label>
                    </div>
                    <div class="form-group form-material floating" data-plugin="formMaterial" id="machineSelection" hidden>
                        <select class="form-control" id="selectMachine" data-plugin="select2" name="machine">
                            <option value="0" selected>Please Select Machine</option>
                        </select>
                    </div>
                    <div class="form-group form-material floating" hidden>
                        <input type="text" id="ipAddress" name="ipAddress">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-40" id="submitButton">Sign in</button>
                </form>
            </div>
        </div>

        <footer class="page-copyright page-copyright-inverse">
            <p>Roto Eye Cloud by PACKAGES LIMITED</p>
            <p>© <?php echo e(date('Y')); ?>. All RIGHT RESERVED.</p>
        </footer>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('footer'); ?>
    <script src="<?php echo e(asset('assets/remark/examples/js/charts/gauges.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/formvalidation/formValidation.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/formvalidation/framework/bootstrap.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/matchheight/jquery.matchHeight-min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/jquery-wizard/jquery-wizard.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/jquery-wizard.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/matchheight.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/remark/examples/js/forms/wizard.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/select2/select2.full.min.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/vendor/bootstrap-select/bootstrap-select.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/select2.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/global/js/Plugin/bootstrap-select.js')); ?>"></script>
    <script src="<?php echo e(asset('assets/custom/secure-me.js')); ?>"></script>
    <script>
        $('#userName').focusin(function() {
            $('#selectMachine').find('option').remove().end().append(
                '<option value="0" selected>Please Select Machine</option>');
        });
        $('#userName').focusout(function() {
            var userName = $('#userName').val();
            $.ajax({
                url: 'check/user/access',
                method: 'GET',
                data: {
                    userName: userName
                },
                statusCode: {
                    200: function(response) {
                        var res = JSON.parse(response);
                        console.log(res);
                        $('#selectMachine')
                            .find('option')
                            .remove()
                            .end()
                            .append('<option value="0" selected>Please Select Machine</option>');
                        for (var i = 0; i < res.allowedMachines.length; i++) {
                            $('#selectMachine').append('<option value="' + res.allowedMachines[i].id +
                                '">' + res.allowedMachines[i].name + '-' + res.allowedMachines[i]
                                .sap_code + '</option>');
                        }
                        if (res.rights[0].rights == 0) {
                            var machineSelection = $('#machineSelection').find(":selected").val();
                            $('#machineSelection').removeAttr('hidden');
                            if (machineSelection == 0) {
                                $('#submitButton').attr('disabled', 'disabled');
                            } else {
                                $('#submitButton').removeAttr('disabled');
                            }
                        } else {
                            $('#machineSelection').attr('hidden', 'hidden');
                            $('#submitButton').removeAttr('disabled');
                        }
                    },
                    500: function(response) {
                        var res = JSON.parse(response);

                    }
                }
            })
        });
        $('#machineSelection').change(function() {
            var machineSelection = $('#machineSelection').find(":selected").val();
            if (machineSelection == 0) {
                $('#submitButton').attr('disabled', 'disabled');
            } else {
                $('#submitButton').removeAttr('disabled');
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.login-layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\xampp\htdocs\rotoeye\resources\views/roto/login.blade.php ENDPATH**/ ?>