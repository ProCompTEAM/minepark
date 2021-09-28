let mypopup = document.getElementById('mypopup');
    button1 = document.getElementById('btn1');
    button2 = document.getElementById('btn2');
    button3 = document.getElementById('btn3');
    button_slider1 = document.getElementById('ButtonSlider1');
    button_slider2 = document.getElementById('ButtonSlider2');
    button_slider3 = document.getElementById('ButtonSlider3');

function openPopup(){
    mypopup.classList.add('openPopup');
    setTimeout(function(){
        mypopup.classList.add('opacity_on')
        mypopup.classList.remove('opacity_off')
    }, 200);
    mypopup.classList.remove('closePopup');
}

function closePopup(){
    mypopup.classList.add('opacity_off');

    setTimeout(function(){
        mypopup.classList.add('closePopup')
        mypopup.classList.remove('opacity_on')
        mypopup.classList.remove('openPopup');
    }, 210);
}

function openButton1() {
    button1.classList.add('button_active')
    button2.classList.remove('button_active')
    button3.classList.remove('button_active');
}

function openButton2() {
    button2.classList.add('button_active');
    button1.classList.remove('button_active');
    button3.classList.remove('button_active');
}

function openButton3() {
    button3.classList.add('button_active');
    button1.classList.remove('button_active');
    button2.classList.remove('button_active');
}

function openButtonSlider1() {
    button_slider1.classList.add('button_active');
    button_slider2.classList.remove('button_active');
    button_slider3.classList.remove('button_active');
}

function openButtonSlider2() {
    button_slider2.classList.add('button_active');
    button_slider3.classList.remove('button_active');
    button_slider1.classList.remove('button_active');
}

function openButtonSlider3() {
    button_slider3.classList.add('button_active');
    button_slider2.classList.remove('button_active');
    button_slider1.classList.remove('button_active');
}