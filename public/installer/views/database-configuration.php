
<!-- top layout here -->

<?php echo cardStart(
    $title = "Database Configuration"
); ?>

<form method="POST" enctype="multipart/form-data" class="m-0" action="/installer/index.php" name="checkDB">

    <?php if (isset($_SESSION['error-message'])) {
        echo "<p class='not-ok check'>" . $_SESSION['error-message'] . '</p>';
    } ?>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="databasedriver">Database Driver</label>
                    <input x-model="databasedriver" id="databasedriver" name="databasedriver" type="text" required
                           value="mysql"
                           class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="databasehost">Database Host</label>
                    <input x-model="databasehost" id="databasehost" name="databasehost" type="text" required
                           value="<?php echo(determineIfRunningInDocker() ? 'mysql' : '127.0.0.1') ?>"
                           class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="databaseport">Database Port</label>
                    <input x-model="databaseport" id="databaseport" name="databaseport" type="number" required
                           value="3306"
                           class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="databaseuser">Database User</label>
                    <input x-model="databaseuser" id="databaseuser" name="databaseuser" type="text" required
                           value="ctrlpaneluser"
                           class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="databaseuserpass">Database User Password</label>
                    <input x-model="databaseuserpass" id="databaseuserpass" name="databaseuserpass" type="text"
                           required
                           class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>

            <div class="form-group">
                <div class="flex flex-col">
                    <label for="database">Database</label>
                    <input x-model="database" id="database" name="database" type="text" required value="ctrlpanel"
                           class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
        </div>
    </div>

    <div class="w-full flex justify-center">
        <button
            class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500"
            name="checkDB">Submit
        </button>
    </div>
</form>

<!-- bottom layout here -->
