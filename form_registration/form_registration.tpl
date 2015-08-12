<form class="form-signin">
  <h2 class="form-signin-heading">Регистрация</h2>
    <?php
      if($v['message']->bool()){
          echo "<div class='alert alert-danger'>".$v['message']."</div>";
      }

    ?>
  <input type="hidden" name="form" value="<?=$v['controller']?>">
  <label for="inputEmail" class="sr-only">Email</label>
  <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email" required autofocus>
  <label for="inputPassword" class="sr-only">Пароль</label>
    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Пароль" required>
    <label for="inputPasswordAgain" class="sr-only">Пароль</label>
    <input type="password" name="password" id="inputPasswordAgain" class="form-control" placeholder="Пароль снова" required>
  <button class="btn btn-lg btn-primary btn-block" type="submit">Зарегистрироваться</button>
</form>