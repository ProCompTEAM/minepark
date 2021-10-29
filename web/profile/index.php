<?php 
    include 'assets/php/connection.php';
    $profile = createRequest("web", "get-user-profile", "Layere");

    $vip = $profile["privilege"];
    $balance = $profile["moneySummary"];
    $numbers = $profile["phoneNumber"];
    $registerData = $profile["createdDate"];
    $registerData = explode(' ', $registerData)[0]; 
    $nik = $profile["fullName"];
    $minute = $profile["minutesPlayed"];

    include 'assets/php/balance.php';
    include 'assets/php/vip_status.php';
    include 'assets/php/nik_status.php';
    include 'assets/php/statistics_day.php';
    include 'assets/php/ban.php';
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="assets/css/popup.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/slider.css">
    <link rel="stylesheet" href="assets/css/preloader.css">
    <link rel="stylesheet" href="assets/css/mobile_menu.css">
    <link rel="shortcut icon" href="../src_mcrpg/logo-small.png" type="image/x-icon">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Inconsolata:wght@700&family=Montserrat:wght@400;900&family=Open+Sans:wght@600;800&display=swap" rel="stylesheet">
    <title>Ваш профиль</title>
</head>
<body>

    <div class="preloader" id="preloader">
        <div class="bubblingG">
            <span id="bubblingG_1"></span>
            <span id="bubblingG_2"></span>
            <span id="bubblingG_3"></span>
        </div>
    </div>
    
    <header class="header">
        <div class="wrapper">
            <div class="menu">
                <a href="/" class="logo" title="Главная страница"></a>
                <nav class="menu__list">
                    <a href="/" class="menu_items">Главная</a>
                    <a href="./developers/developers.html" class="menu_items">Разработчики</a>
                    <a href="./about_profile.html" class="menu_items">О проекте</a>
                    <a target="_blank" href="http://vk.com/mcperp" class="menu__items">Мы ВКОНТАКТЕ</a>
                </nav>
                <div class="navigation_menu">
                    <nav class="menu_user">
                        <ul class="menu_list">
                            <li class="li__user">
                                <div class="link_user">
                                    <div href="#" class="menu_link_user"></div>
                                    <div href="#" class="arrow"></div>
                                </div>
                                <ul class="sub-menu__list">
                                    <li>
                                        <a target="_blank" href="https://vk.me/mcperp" class="menu_link">Сообщить о проблеме</a>
                                    </li>
                                    <li>
                                        <a class="button_server" onclick="openPopup();">Выбрать сервер</a>
                                    </li>
                                    <li>
                                        <a href="#" class="menu_link">Выйти</a>
                                    </li>
                                </ul>
                            </li>
                        </ul>
                    </nav>
                </div>
            </div>
            <div class="menu_mobile">
                <div class="linear">
                    <div class="LineMB_1"></div>
                    <div class="LineMB_2"></div>
                    <div class="LineMB_3"></div>
                </div>
                <div class="menu_list_mobile">
                    <nav class="menu__listMB">
                        <a href="/" class="menu_itemsMB">Главная</a>
                        <a href="./developers/developers.html" class="menu_itemsMB">Разработчики</a>
                        <a href="./about_profile.html" class="menu_itemsMB">О проекте</a>
                        <a target="_blank" href="http://vk.com/mcperp" class="menu_itemsMB">Мы ВКОНТАКТЕ</a>
                    </nav>
                    <div class="navigation_menuMB">
                        <nav class="menu_user">
                            <ul class="menu_list">
                                <li class="li__user">
                                    <div class="link_user">
                                        <div href="#" class="menu_link_user"></div>
                                        <div href="#" class="arrow"></div>
                                    </div>
                                    <ul class="sub-menu__list">
                                        <li>
                                            <a target="_blank" href="https://vk.me/mcperp" class="menu_link">Сообщить о проблеме</a>
                                        </li>
                                        <li>
                                            <a id="button_server" onclick="openPopup();">Выбрать сервер</a>
                                        </li>
                                        <li>
                                            <a href="#" class="menu_link">Выйти</a>
                                        </li>
                                    </ul>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <section class="information_user">
        <div class="wrapper">
            <h1>О вашем профиле</h1>
            <div class="information">
                <div class="user_photo">
                    <div class="user_data_photo">
                        <button class="link_image">Изменить изображение</button>
                    </div>
                </div>
                <div class="info">
                    <div class="info_user">
                        <p class="info__user margin_right">НИК: </p>
                        <p class="user__info"><?php print_r($nik); ?></p>
                    </div>
                    <div class="info_user height_29">
                        <p class="info__user_data_reg margin_right">ДАТА  РЕГИСТРАЦИИ:</p>
                        <p class="user__data"><?php print_r($registerData); ?></p>
                    </div>
                    <div class="info_user width_400">
                        <p class="info__user_balance margin_right">ОБЩИЙ БАЛАНС СЧЁТА:</p>
                        <p class="user__data_balance"><?php print_r($balance_str); ?></p>
                    </div>
                    <div class="info_user width_313">
                        <p class="info__user_role margin_right">VIP: </p>
                        <p class="user__data_role"><?php print_r($vip_status); ?></p>
                    </div>
                     <div class="info_user width_313">
                        <p class="info__user_work margin_right">НОМЕР ТЕЛЕФОНА: </p>
                        <p class="user__data_work"><?php print_r($numbers); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class="information_data">
        <div class="wrapper">
            <div class="line_white"></div>
            <div class="information_user_1">
                <div class="info_user_1">
                    <div class="column">
                        <div class="column_1">
                            <div class="day">
                                <p class="text_information_1">кол-во времени в игре</p>
                                <h2 class="h2_information width_181 cursor_pointer" title="<?php print_r($title)?>"> <?php print_r($day);?> </h2>
                            </div>
                            <div class="score">
                                <p class="text_information_1">счетов в банке</p>
                                <h2 class="h2_information width_31">1</h2>
                            </div>
                        </div>
                        <div class="column_2">
                            <div class="apartments">
                                <p class="text_information_1">квартир</p>
                                <h2 class="h2_information width_21 height_61">1</h2>
                            </div>
                            <div class="auto">
                                <p class="text_information_1 margin_0">машин</p>
                                <h2 class="h2_information width_21 height_61">1</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="line_black"></div>
            <div class="information_user_1">
                <div class="info_user_1">
                    <div class="column">
                        <div class="column_1">
                            <div class="day">
                                <p class="text_information_1">кольчиство дней в игре</p>
                                <h2 class="h2_information">12д.</h2>
                            </div>
                            <div class="score">
                                <p class="text_information_1">счетов в банке</p>
                                <h2 class="h2_information width_31">1</h2>
                            </div>
                        </div>
                        <div class="column_2">
                            <div class="apartments">
                                <p class="text_information_1">квартир</p>
                                <h2 class="h2_information width_21 height_61">1</h2>
                            </div>
                            <div class="auto">
                                <p class="text_information_1 margin_0">машин</p>
                                <h2 class="h2_information width_21 height_61">1</h2>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="line_white_2"></div>
        </div>
    </section>
    <footer>
        <div class="footer_menu">
            <div class="wrapper">
                <div class="menu_footer">
                    <nav class="menu__list_footer">
                        <a href="/" class="menu_items margin_left_44">Главная</a>
                        <a href="./developers/developers.html" class="menu_items">Разработчики</a>
                        <a href="./about_profile.html" class="menu_items">О проекте</a>
                        <a target="_blank" href="http://vk.com/mcperp" class="menu__items">Мы ВКОНТАКТЕ</a>
                    </nav>
                </div>
            </div>
        </div>
    </footer>

    <div class="information_server" id="mypopup">
        <div class="container">
            <div class="content_destop">
                <div class="linear_1"></div>
                <p class="text_server">Выберите сервер:</p>
                <div class="cross" id="close" onclick="closePopup();">
                    <a title="Закрыть">
                        <div class="line_1"></div>
                        <div class="line_2"></div>
                    </a>
                </div>
                <div class="InformationBlock">
                    <div class="ContentBlock display_none">
                        <div class="RP_logo">
                            <div class="img_RP"></div>
                            <p class="text_RP">Городской режим</p>
                        </div>
                        <div class="button" onclick="openButton1()">
                            <p class="TextButtonBlock1" id="btn1"></p>
                        </div>
                    </div>
                    <div class="ContentBlock">
                        <div class="RP_logo">
                            <div class="img_RP"></div>
                            <p class="text_RP">Городской режим</p>
                        </div>
                        <div class="button">
                            <p class="TextButtonBlock2 button_active" id="btn2" onclick="openButton2()"></p>
                        </div>
                    </div>
                    <div class="ContentBlock display_none">
                        <div class="RP_logo">
                            <div class="img_RP"></div>
                            <p class="text_RP">Городской режим</p>
                        </div>
                        <div class="button">
                            <p class="TextButtonBlock3" id="btn3" onclick="openButton3()"></p>
                        </div>
                    </div>
                </div>
                <div class="linear_2"></div>
            </div>

            <div class="slider">
                <div class="slider__items">
                    <div class="information-block">
                        <div class="linear_1"></div>
                        <p class="text_server">Выберите сервер:</p>
                        <div class="cross" id="close" onclick="closePopup();">
                            <a title="Закрыть">
                                <div class="line_1"></div>
                                <div class="line_2"></div>
                            </a>
                        </div>
                    </div>
                    <div class="InformationBlock">
                        <div class="arrow_left" id="btn">
                            <div class="LineLeft1"></div>
                            <div class="LineLeft2"></div>
                        </div>
                        <div class="Sliders curry">
                            <div class="RP_logo">
                                <div class="img_RP"></div>
                                <p class="text_RP">Городской режим 1</p>
                            </div>
                            <div class="button">
                                <p class="TextButtonBlock1 button_active" id="ButtonSlider1" onclick="openButtonSlider1()"></p>
                            </div>
                        </div>
                        <div class="Sliders">
                            <div class="RP_logo">
                                <div class="img_RP"></div>
                                <p class="text_RP">Городской режим 2</p>
                            </div>
                            <div class="button">
                                <p class="TextButtonBlock2" id="ButtonSlider2" onclick="openButtonSlider2()"></p>
                            </div>
                        </div>
                        <div class="Sliders">
                            <div class="RP_logo">
                                <div class="img_RP"></div>
                                <p class="text_RP">Городской режим 3</p>
                            </div>
                            <div class="button">
                                <p class="TextButtonBlock3" id="ButtonSlider3" onclick="openButtonSlider3()"></p>
                            </div>
                        </div>
                        <div class="arrow_right" id="btn">
                            <div class="LineRight1"></div>
                            <div class="LineRight2"></div>
                        </div>
                    </div>
                    <div class="linear_2"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="InfarmationBan" id="infoban">
        <div class="container_ban">
            <a href="#" class="link_exit">выйти из профиля</a>
            <div class="img_ban"></div>
            <p class="text_ban">К сожилению вы были забанены</p>
            <div class="reason">
                <p class="text_reason">причина бана: <span class="reason">8. Помеха администрации</span></p>
            </div>
            <p class="TextBaNotConsent">Если вы не согласны с баном <br> или бан выдан по ошибке</p>
            <a target="_blank" href="https://vk.me/mcperp" class="button_ban">напишите нам!</a>
        </div>
    </div>

    <style>
        .width_181 {
            width: <?php print_r($width_style); ?>;
        }
        .InfarmationBan {
            display: <?php print_r($style_ban)?>;
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.2.1.min.js"></script>
    <script src="assets/js/popup.js"></script>
    <script src="assets/js/preloader.js"></script>
    <script src="assets/js/slider.js"></script>
</body>
</html>