
<!-- top layout here -->

<?php echo cardStart(
    $title = "First Admin Creation",
    $subtitle = "Lets create the first admin user!"
); ?>

<form method="POST" enctype="multipart/form-data" class="m-0" action="/installer/index.php" name="createUser">

    <?php if (isset($_SESSION['error-message'])) {
        echo "<p class='not-ok check'>" . $_SESSION['error-message'] . '</p>';
    } ?>

    <div class="form-group">
        <div class="flex flex-col mb-3">
            <label for="pteroID">Pterodactyl User ID </label>
            <input id="pteroID" name="pteroID" type="text" required value="1"
                class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
            <span class="text-neutral-400">Found in the users-list on your pterodactyl dashboard</span>
        </div>
    </div>

    <div class="form-group">
        <div class="flex flex-col mb-3">
            <label for="pass">Password</label>
            <input id="pass" name="pass" type="password" required value="" minlength="8"
                class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
            <span class="text-neutral-400">This will be your new pterodactyl password aswell!</span>
        </div>
    </div>
    <div class="form-group">
        <div class="flex flex-col">
            <label for="repass">Confirm Password</label>
            <input id="repass" name="repass" type="password" required value="" minlength="8"
                class="px-2 py-1 bg-[#1D2125] border-2 focus:border-sky-500 box-border rounded-md border-transparent outline-none">
        </div>
    </div>

    <div class="w-full flex justify-center">
        <button
            class="w-1/3 min-w-fit mt-2 px-4 py-2 font-bold rounded-md bg-sky-500 hover:bg-sky-600 shadow-sky-400 focus:outline-2 focus:outline focus:outline-offset-2 focus:outline-sky-500"
            name="createUser">Submit
        </button>
    </div>
</form>

<!-- bottom layout here -->
