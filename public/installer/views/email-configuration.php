
<!-- top layout here -->

<?php echo cardStart(
    $title = "E-Mail Configuration",
    $subtitle = "This process might take a few seconds when submitted.<br>Please do not refresh or close this page!"
); ?>

<form method="POST" enctype="multipart/form-data" class="m-0" action="/installer/index.php" name="checkSMTP">

    <?php if (isset($_SESSION['error-message'])) {
        echo "<p class='not-ok check'>" . $_SESSION['error-message'] . '</p>';
    } ?>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="method">E-Mail Protocol</label>
                    <select id="method" name="method" required class="px-2 py-2 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                        <option value="smtp" selected>SMTP</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="host">Your Mailer-Host</label>
                    <input id="host" name="host" type="text" required value="smtp.google.com" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>

            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="port">Your Mail Port</label>
                    <input id="port" name="port" type="number" required value="567" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>

            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="user">Your Mail User</label>
                    <input id="user" name="user" type="text" required value="info@mydomain.com" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>

            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="pass">Your Mail-User Password</label>
                    <input id="pass" name="pass" type="password" required value="" class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>

            <div class="form-group">
                <div class="flex flex-col">
                    <label for="encryption">Your Mail encryption method</label>
                    <select id="encryption" name="encryption" required class="px-2 py-2 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                        <option value="tls" selected>TLS</option>
                        <option value="ssl">SSL</option>
                        <option value="null">None</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="flex w-full justify-around mt-4 gap-8 px-8">
        <button type="submit"
                class="w-full px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500"
                name="checkSMTP">Submit
        </button>

        <a href="?step=7" class="w-full">
            <button type="button" class="w-full px-4 py-2 font-bold rounded-md bg-yellow-500/90 hover:bg-yellow-600 shadow-yellow-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-yellow-600">
                Skip For Now
            </button>
        </a>
    </div>
</form>

<!-- bottom layout here -->
