jQuery(document).ready(function($)
{
    $("#tweet").keyup(function()
    {
        var box=$(this).val();
        var main = box.length *100;
        var value= (main / 140);
        var count= 140 - box.length;

        $('#count').html(count);
        if(box.length <= 140)
        {
            $('#bar').css("background-color", "#5fbbde");
            $('#bar').css("width",value+'%');
        }
        else
        {
            $('#bar').css("width",'100%');
            $('#bar').css("background-color", "#f11");
            alert('Character Limit Exceeded!');
        }
        return false;
    });
});