
<!-- top layout here -->

<?php echo cardStart(
    $title = "Redis Configuration"
); ?>

<form method="POST" enctype="multipart/form-data" class="m-0" action="/installer/forms.php" name="redisSetup">

    <?php if (isset($_GET['message'])) {
        echo "<p class='not-ok check'>" . $_GET['message'] . '</p>';
    } ?>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="redishost">Redis Host</label>
                    <input x-model="redishost" id="redishost" name="redishost" type="text" required
                            value="<?php echo(determineIfRunningInDocker() ? 'redis' : '127.0.0.1') ?>"
                            class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="redisport">Redis Port</label>
                    <input x-model="redisport" id="redisport" name="redisport" type="number" required
                            value="6379"
                            class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
            <div class="form-group">
                <div class="flex flex-col mb-3">
                    <label for="redispassword">Redis Password (optionally, only if configured)</label>
                    <input x-model="redispassword" id="redispassword" name="redispassword" type="text"
                            placeholder="usually can be left blank"
                            class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
                </div>
            </div>
        </div>
    </div>

    <div class="w-full flex justify-center">
        <button
            class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500"
            name="redisSetup">Submit
        </button>
    </div>
</form>

<!-- bottom layout here -->
