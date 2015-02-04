<?php
//	License for all code of this FreePBX module can be found in the license file inside the module directory
//	Copyright 2013 Schmooze Com Inc.
//
echo '
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-9">
            <div class="fpbx-container">
';

$cel = FreePBX::Cel();
echo $cel->myShowPage();

echo '
            </div>
        </div>
    </div>
</div>
';
