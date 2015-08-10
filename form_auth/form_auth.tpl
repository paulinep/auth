  <form class="form-signin">
    <h2 class="form-signin-heading">Вход на сайт</h2>
      <?php
        if($v['message']->bool()){
            echo "<div class='alert alert-danger'>".$v['message']."</div>";
        }

      ?>
    <input type="hidden" name="form" value="<?=$v['controller']?>">
    <label for="inputEmail" class="sr-only">Email</label>
    <input type="email" name="email" id="inputEmail" class="form-control" placeholder="Email address" required autofocus>
    <label for="inputPassword" class="sr-only">Пароль</label>
    <input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" required>
    <div class="checkbox">
      <label>
        <input type="checkbox" name="remember-me" value="remember-me"> Запомнить меня
      </label>
    </div>
    <button class="btn btn-lg btn-primary btn-block" type="submit">Войти</button>
     <button class="btn btn-lg btn-primary btn-block" type="button">Зарегистрироваться</button>
  </form>
