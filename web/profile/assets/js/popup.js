let mypopup = document.getElementById('mypopup'); 

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