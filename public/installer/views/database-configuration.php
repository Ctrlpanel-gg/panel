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
                        placeholder="e.g. localhost, 10.x.x.x, mysql, 192.168.x.x"
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

    <hr style="border: none; height: 3px; background-color: rgba(0, 0, 0, 0.3); border-bottom: 1px; border-radius: 1px; ; margin-top: 30px !important; margin-bottom: 30px">

    <div class="w-full flex justify-between items-center mt-4">
        <?php
        if ($_SESSION['is_previous_button_available'] == true) {
        ?>
            <a href="?step=previous" class="flex items-center px-4 py-2 font-bold rounded-md bg-red-500 hover:bg-red-400 shadow-red-200 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-red-500">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="mr-1">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M9.586 4l-6.586 6.586a2 2 0 0 0 0 2.828l6.586 6.586a2 2 0 0 0 2.18 .434l.145 -.068a2 2 0 0 0 1.089 -1.78v-2.586h7a2 2 0 0 0 2 -2v-4l-.005 -.15a2 2 0 0 0 -1.995 -1.85l-7 -.001v-2.585a2 2 0 0 0 -3.414 -1.414z" />
                </svg>
                Back
            </a>
        <?php
        } else {
        ?>
            <button type="button" id="backButton" class="flex items-center px-4 py-2 font-bold rounded-md bg-gray-200 text-gray-500 shadow-inner cursor-not-allowed" disabled>
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="mr-1">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                    <path d="M9.586 4l-6.586 6.586a2 2 0 0 0 0 2.828l6.586 6.586a2 2 0 0 0 2.18 .434l.145 -.068a2 2 0 0 0 1.089 -1.78v-2.586h7a2 2 0 0 0 2 -2v-4l-.005 -.15a2 2 0 0 0 -1.995 -1.85l-7 -.001v-2.585a2 2 0 0 0 -3.414 -1.414z" />
                </svg>
                Back
            </button>
        <?php
        }
        ?>

        <button type="submit" class="flex items-center px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500" name="checkDB">
            Next
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" class="ml-1">
                <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                <path d="M12.089 3.634a2 2 0 0 0 -1.089 1.78l-.001 2.586h-6.999a2 2 0 0 0 -2 2v4l.005 .15a2 2 0 0 0 1.995 1.85l6.999 -.001l.001 2.587a2 2 0 0 0 3.414 1.414l6.586 -6.586a2 2 0 0 0 0 -2.828l-6.586 -6.586a2 2 0 0 0 -2.18 -.434l-.145 .068z" />
            </svg>
        </button>
    </div>
</form>

<!-- bottom layout here -->