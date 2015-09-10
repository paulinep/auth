/**
 * Created by pauline on 14.08.15.
 */
$(function(){
    var message='';
    var form = $('form.form-signin');
    var result = 0;

    //выводит сообщение
    function setMessage(message){
        if(message.length>0){
            if($("div.alert").length==0){
                $(".form-signin-heading").after("<div class='alert alert-danger'>"+message+"</div>");
            }
        }else{
            if(form.has("div.alert alert-danger")){
                $("div.alert").remove();
                message ='';
            }

        }
    }
    $('input#inputEmail').blur(function(e){
        $.ajax({
            method: "POST",
            data: { call: "check", form: $('input[name="form"]').attr('value'),  email: this.value },
            dataType: "json"
        }).done(function(data) {
            result = data.result;
            switch (result){
                case 1:
                 message='';
                    setMessage(message);
                    break;
                case 2:
                 message="Такой пользователь уже существует";
                    setMessage(message);
                    break;
                case 3:
                 message="Некорректный адрес электронной почты";
                    setMessage(message);
                    break;
                default:
                 message="Произошла неизвестная ошибка";
                    setMessage(message);
                    break

            }


        });

    });
    form.submit(function(e){
        var password = document.getElementById("inputPassword").value;
        if(document.getElementById("inputPasswordAgain").value!==password){
            message="Пароли не совпадают";
            setMessage(message);
            e.preventDefault();
        }else{
            message='';
            setMessage(message);
            return;
        }
        $('input#inputEmail').trigger('blur');
        console.log(result);

    });




});
