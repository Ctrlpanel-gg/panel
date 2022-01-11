<?php
include ("functions.php");
?>

<html>
  <head>
    <title>Controlpanel.gg installer Script</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <style>
		body {background-color: powderblue;}

		.card {
    position: absolute;
    top: 50%;
    left: 50%;
    margin-right: -50%;
    transform: translate(-50%, -50%);
		}
		.ok{
			color: green;
		}
		.ok::before{
			 content: "✔️";
		}
		.notok{
			color: red;
		}
		.notok::before{
			 content: "❌";
		}
	</style>
  </head>
  <body>

<?php if(!isset($_GET['step'])){ ?>
        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <b class="mr-1">Controlpanel.GG</b>
            </div>

                <div class="card-body">
                    <p class="login-box-msg">This installer will lead you through the most crucial Steps of Controlpanel.gg`s setup</p>

                    <p class="<?php print(checkPhpVersion()==="OK"?"ok":"notok");?>">  php version: <?php echo phpversion();?> (required <?php echo $requirements["php"];?>)</p>
                    <p class="<?php print(getMySQLVersion()==="OK"?"ok":"notok");?>">  mysql version: <?php echo getMySQLVersion();?> (required <?php echo $requirements["mysql"];?>)</p>

                    <p class="<?php print(sizeof(checkExtensions()) == 0?"ok":"notok");?>"> Missing extentions: <?php print(sizeof(checkExtensions()) == 0?"None":"");foreach(checkExtensions() as $ext){ echo $ext.", ";};?> (try to install anyway)</p>

                    <p class="<?php print(getZipVersion()==="OK"?"ok":"notok");?>">  Zip version: <?php echo getZipVersion();?> </p>

                    <p class="<?php print(getGitVersion()==="OK"?"ok":"notok");?>">  Git version: <?php echo getGitVersion();?> </p>

                    <p class="<?php print(getTarVersion()==="OK"?"ok":"notok");?>">  Tar version: <?php echo getTarVersion();?> </p>


                        <a href="?step=2"><button class="btn btn-primary">Lets go</button></a>
                </div>
            </div>

<?php 
}
if (isset($_GET['step']) && $_GET['step']==2){

	?>

        <div class="card card-outline card-primary">
            <div class="card-header text-center">
                <b class="mr-1">Controlpanel.GG</b>
            </div>

                <div class="card-body">
                    <p class="login-box-msg">Lets start with your Database</p>
                  <?php if(isset($_GET['message'])){
                    	echo "<p class='notok'>".$_GET['message']."</p>";
                    }
                    ?>

               <form method="POST" enctype="multipart/form-data" class="mb-3"
                          action="/install/forms.php" name="checkDB">

                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <div class="custom-control mb-3">
                                        <label for="database">Database Driver</label>
                                        <input x-model="databasedriver" id="databasedriver" name="databasedriver"
                                               type="text" required
                                               value="mysql" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control mb-3">
                                        <label for="databasehost">Database Host</label>
                                        <input x-model="databasehost" id="databasehost" name="databasehost" type="text"
                                               required
                                               value="127.0.0.1" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control mb-3">
                                        <label for="databaseport">Database Port</label>
                                        <input x-model="databaseport" id="databaseport" name="databaseport"
                                               type="number" required
                                               value="3306" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control mb-3">
                                        <label for="databaseuser">Database User</label>
                                        <input x-model="databaseuser" id="databaseuser" name="databaseuser" type="text"
                                               required
                                               value="dashboarduser" class="form-control">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="custom-control mb-3">
                                        <label for="databaseuserpass">Database User Password</label>
                                        <input x-model="databaseuserpass" id="databaseuserpass" name="databaseuserpass"
                                               type="text" required
                                               class="form-control ">
                                    </div>
                                </div>

                                <div class="form-group">
                                    <div class="custom-control mb-3">
                                        <label for="database">Database</label>
                                        <input x-model="database" id="database" name="database" type="text" required
                                               value="dashboard" class="form-control">
                                    </div>
                                </div>

                                </div>
 
                                <button class="btn btn-primary" name="checkDB">Submit</button>
                            </div>
                        </div>


                </div>
            </div>
	<?php
}

?>



<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
  </body>
</html>