$(document).ready(function(){
    var calculate = setInterval(function(){
        /* query the completion percentage from the server */
        $.get("{{ path('process_calculate') }}", function(data){
            data = JSON.parse(data);
            /* update the progress bar width */
            $("#progress").css('width',data.value+'%');
            $("#progressdesc").text(data.desc);
            /* test to see if the job has completed */
            if(data.finished == 1) {
                $("#progress").css('width',data.value+'%');
                $("#progressdesc").text("Completed");
                clearInterval(calculate);
                $("#progressouter").removeClass("active");
            }
        })
    }, 1000);
});
