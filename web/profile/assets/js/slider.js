$(document).ready(function(){
    $('.arrow_right').click(function(){
        let currentBlock = $('.Sliders.curry');
            currentBlockIndex = $('.Sliders.curry').index();
            nextBlockIndex = currentBlockIndex;
            nextBlock = $('.Sliders').eq(nextBlockIndex);


        currentBlock.fadeOut(1);
        currentBlock.removeClass('curry');

        if(currentBlockIndex == ($('.Sliders:last').index())){
            $('.Sliders').eq(0).fadeIn(1);
            $('.Sliders').eq(0).addClass('curry');
        } else {
            nextBlock.fadeIn(1);
            nextBlock.addClass('curry');
        }
    });
    $('.arrow_left').click(function(){
        let currentBlock = $('.Sliders.curry');
            currentBlockIndex = $('.Sliders.curry').index();
            prevBlockIndex = currentBlockIndex - 2;
            prevBlock = $('.Sliders').eq(prevBlockIndex);

        currentBlock.fadeOut(1);
        currentBlock.removeClass('curry');
        prevBlock.fadeIn(1);
        prevBlock.addClass('curry');
    });
});