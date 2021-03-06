<?php 

if (!isset($_REQUEST)) { 
  return; 
} 

//Строка для подтверждения адреса сервера из настроек Callback API 
$confirmation_token = '2e15d6b8'; 

//Ключ доступа сообщества 
$token = '53b317eb2275bc9c1ae3e889f86e19d153edbbcbda98baf2334608f98b88c1c6bb88d90c911fb7e573eb6'; 

//Получаем и декодируем уведомление 
$data = json_decode(file_get_contents('php://input')); 

//Проверяем, что находится в поле "type" 
switch ($data->type) { 
  //Если это уведомление для подтверждения адреса... 
  case 'confirmation': 
    //...отправляем строку для подтверждения 
    echo $confirmation_token; 
    break; 

//Если это уведомление о новом сообщении... 
  case 'message_new': 
  	$message = "";
  	$yesNo = '<br>1. Да<br>2. Нет';
    $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0")); 
  	$noMessage = 'Нет такого варианта ответа';
    $body = $data->object->body;
  //...получаем id его автора 
    $first_name = $user_info->response[0]->first_name;
    $last_name = $user_info->response[0]->last_name;
    $sex = $user_info->response[0]->sex;
    $user_id = $data->object->user_id;
    if($sex == '1'){
    	$sex = 'Ж';
    }else{
    	$sex = 'М';
    }
  // Коннектимся к базе
    $mysqli = new mysqli("localhost","id1939899_top4ek","q2w3e4r5","id1939899_top4ek");
    $mysqli->set_charset("utf8");
    // Проверка на наличие в бд пользователя
    $res = $mysqli->query("SELECT `user_id` FROM `vitalert` WHERE user_id = $user_id");
    $count = mysqli_num_rows($res);
    if($count == 0){
      $createUser = $mysqli->query("INSERT INTO `vitalert`(`id`, `user_id`, `status`, `first_name`, `last_name`) VALUES (NULL, '$user_id', '0', '$first_name', '$last_name')");
      $message = "Здравствуйте, что у вас случилось?";
    }
    // Получаем статус и работаем с ним
    $status = checkStatus($mysqli,$user_id); 
    switch ($status) {
    	case '0':
      		$message = "Здравствуйте, что с вами не так?";
    		$sql = $mysqli->query("UPDATE `vitalert` SET `status`= 1 WHERE `user_id` = $user_id");
    		break;
    	case '1':
    		$message = 'Вы успешно зарегистрированы. Пройдите, пожалуйста быстрый опрос.<br>Сколько вам лет?(напишите цифру)';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `status`= 2 WHERE `user_id` = $user_id");
    		break;
    	case '2':
    		$message = 'Вам '.$body.'?'.$yesNo.'';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `age`= '$body', `status` = 3 WHERE `user_id` = $user_id");
    		break;
    	case '3':
    		if($body == '1'){
    			$message = 'Ваш вес в кг? (напишите цифру)';
    			$sql = $mysqli->query("UPDATE `vitalert` SET `status`= 4 WHERE `user_id` = $user_id");
    		}elseif($body == '2'){
    			$message = 'Сколько вам лет?(напишите цифру)';
    			$sql = $mysqli->query("UPDATE `vitalert` SET `status`= 2 WHERE `user_id` = $user_id");
    		}else{
    			$message = $noMessage;
    		}
    		break;
    	case '4':
    		$message = 'Вы весите '.$body.'?'.$yesNo.'';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `width` = '$body', `status`= 5 WHERE `user_id` = $user_id");
    		break;
    	case '5':
    		if($body == '1'){
    			$message = 'Как вы оцениваете свое самочувствие?<br>1. Отличное<br>2. Хорошее<br>3. Удовлетворительное<br>4. Плохое<br>5. Ужасное';
    			$sql = $mysqli->query("UPDATE `vitalert` SET `status`= 6 WHERE `user_id` = $user_id");
    		}elseif($body == '2'){
    			$message = 'Ваш вес в кг? (напишите цифру)';
    			$sql = $mysqli->query("UPDATE `vitalert` SET `status`= 4 WHERE `user_id` = $user_id");
    		}else{
    			$message =  $noMessage;
    		}
    		break;
    	case '6':
    		$message = 'Ваш вид работы ?<br>1. Полностью неактивный (постельный режим)<br> 2. Сидячий (сидячая малоподвижная работа, низкий уровень физических нагрузок)<br>3. Стоячая работа с низкой подвижностью<br>4. Сидячая и стоячая работа с умеренной подвижностью<br>5. Тяжелый физический труд, высокий уровень физических нагрузок в течение дня';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `healthStat` = '$body', `status`= 7 WHERE `user_id` = $user_id");
    		break;
    	case '7':
    		$message = 'Занимаетесь ли вы дополнительной активностью (фитнес, тренажерный зал, бег и т.д.)?'.$yesNo.'';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `workType` = '$body', `status`= 8 WHERE `user_id` = $user_id");
    		break;
    	case '8':
    		if($body == '1'){
    			$message = 'Количество занятий в неделю?(напишите цифру)';
    			$sql = $mysqli->query("UPDATE `vitalert` SET `activity` = '$body', `status`= 9 WHERE `user_id` = $user_id");
    		}elseif($body == '2'){
    			$message = 'Ваш тип сна<br>1. Беспокойный<br>2. Нормальный<br>3. С пробуждениями<br>4. Глубокий';
    			$sql = $mysqli->query("UPDATE `vitalert` SET `activity` = '$body', `status`= 13 WHERE `user_id` = $user_id");
    		}else{
    			$message = $noMessage;
    		}
    		break;
    	case '9':
    		$message = 'Вид активности?<br>1. Бег<br>2. Ходьба<br>3. Фитнес<br>4. Силовой тренинг<br>5. Плавание';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `trainCount` = '$body', `status`= 10 WHERE `user_id` = $user_id");
    		break;
    	case '10':
    		$message = 'Продолжительность занятий в минутах?';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `activityType` = '$body', `status`= 11 WHERE `user_id` = $user_id");
    		break;
    	case '11':
    		$message = 'Тип нагрузки от 1(Легкая) до 3(Тяжелая)';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `trainDuration` = '$body', `status`= 12 WHERE `user_id` = $user_id");
    		break;
    	case '12':
    		$message = 'Ваш тип сна<br>1. Беспокойный<br>2. Нормальный<br>3. С пробуждениями<br>4. Глубокий';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `loadType` = '$body', `status`= 13 WHERE `user_id` = $user_id");
    		break;
    	case '13':
    		$message = 'Есть ли у вас вредные привычки?'.$yesNo.'';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `sleep` = '$body', `status`= 14 WHERE `user_id` = $user_id");
    		break;
    	case '14':
    		$message = 'Спасибо за опрос :)';
    		$sql = $mysqli->query("UPDATE `vitalert` SET `habits` = '$body' WHERE `user_id` = $user_id");
    		break;
    	default:
    		# code...
    		break;
    }
    //затем с помощью users.get получаем данные об авторе 
    $user_info = json_decode(file_get_contents("https://api.vk.com/method/users.get?user_ids={$user_id}&v=5.0")); 

//и извлекаем из ответа его имя 
    $user_name = $user_info->response[0]->first_name; 

//С помощью messages.send отправляем ответное сообщение 
    $request_params = array( 
      'message' => $message, 
      'user_id' => $user_id, 
      'access_token' => $token, 
      'v' => '5.0' 
    ); 

$get_params = http_build_query($request_params); 

file_get_contents('https://api.vk.com/method/messages.send?'. $get_params); 

//Возвращаем "ok" серверу Callback API 

echo('ok'); 

break; 

} 
// Проверка статуса
function checkStatus($mysqli,$user_id){
  $sql = $mysqli->query("SELECT user_id, status FROM `vitalert`");
  if($sql->num_rows > 0) {
      while($row = $sql->fetch_assoc()) {
        if($row['user_id'] == $user_id){
          return $row['status'];
        }
      }
  }
}
?> 