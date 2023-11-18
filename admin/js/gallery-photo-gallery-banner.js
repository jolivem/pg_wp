(function( $ ) {
	'use strict';
    $(document).ready(function (){
        var checkCountdownIsExists = $(document).find('#ays-gpg-countdown-main-container');
        if ( checkCountdownIsExists.length > 0 ) {
            var second  = 1000,
                minute  = second * 60,
                hour    = minute * 60,
                day     = hour * 24;

            var gpgCountdownEndTime = galleryLangObj.gpgBannerDate;
            // var gpgCountdownEndTime = "DEC 31, 2022 23:59:59";
            var countDown_new = new Date(gpgCountdownEndTime).getTime();

            if ( isNaN(countDown_new) || isFinite(countDown_new) == false ) {
                var AYS_GALLERY_MILLISECONDS = 3 * day;
                var countdownStartDate = new Date(Date.now() + AYS_GALLERY_MILLISECONDS);
                var gpgCountdownEndTime = countdownStartDate.aysGalleryCustomFormat( "#YYYY#-#MM#-#DD# #hhhh#:#mm#:#ss#" );
                var countDown_new = new Date(gpgCountdownEndTime).getTime();
            }

            aysGpgBannerCountdown();

            var y = setInterval(function() {

                var now = new Date().getTime();
                var distance_new = countDown_new - now;                

                aysGpgBannerCountdown();

                //do something later when date is reached
                if (distance_new < 0) {
                    var headline  = document.getElementById("ays-gpg-countdown-headline"),
                        countdown = document.getElementById("ays-gpg-countdown"),
                        content   = document.getElementById("ays-gpg-countdown-content");

                  // headline.innerText = "Sale is over!";
                  countdown.style.display = "none";
                  content.style.display = "block";

                  clearInterval(y);
                }
            }, 1000);
        }

        function aysGpgBannerCountdown(){
            var now = new Date().getTime();
            var distance_new = countDown_new - now;

            var countDownDays    = document.getElementById("ays-gpg-countdown-days");
            var countDownHours   = document.getElementById("ays-gpg-countdown-hours");
            var countDownMinutes = document.getElementById("ays-gpg-countdown-minutes");
            var countDownSeconds = document.getElementById("ays-gpg-countdown-seconds");

            if((countDownDays !== null || countDownHours !== null || countDownMinutes !== null || countDownSeconds !== null) && distance_new > 0){

                var countDownDays_innerText    = Math.floor(distance_new / (day));
                var countDownHours_innerText   = Math.floor((distance_new % (day)) / (hour));
                var countDownMinutes_innerText = Math.floor((distance_new % (hour)) / (minute));
                var countDownSeconds_innerText = Math.floor((distance_new % (minute)) / second);

                if( isNaN(countDownDays_innerText) || isNaN(countDownHours_innerText) || isNaN(countDownMinutes_innerText) || isNaN(countDownSeconds_innerText) ){
                    var headline  = document.getElementById("ays-gpg-countdown-headline"),
                        countdown = document.getElementById("ays-gpg-countdown"),
                        content   = document.getElementById("ays-gpg-countdown-content");

                    // headline.innerText = "Sale is over!";
                    countdown.style.display = "none";
                    content.style.display = "block";

                    // clearInterval(y);
                } else {
                    countDownDays.innerText    = countDownDays_innerText;
                    countDownHours.innerText   = countDownHours_innerText;
                    countDownMinutes.innerText = countDownMinutes_innerText;
                    countDownSeconds.innerText = countDownSeconds_innerText;
                }
                
            }
        }

        Date.prototype.aysGalleryCustomFormat = function( formatString){
            var YYYY,YY,MMMM,MMM,MM,M,DDDD,DDD,DD,D,hhhh,hhh,hh,h,mm,m,ss,s,ampm,AMPM,dMod,th;
            YY = ((YYYY=this.getFullYear())+"").slice(-2);
            MM = (M=this.getMonth()+1)<10?('0'+M):M;
            MMM = (MMMM=["January","February","March","April","May","June","July","August","September","October","November","December"][M-1]).substring(0,3);
            DD = (D=this.getDate())<10?('0'+D):D;
            DDD = (DDDD=["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"][this.getDay()]).substring(0,3);
            th=(D>=10&&D<=20)?'th':((dMod=D%10)==1)?'st':(dMod==2)?'nd':(dMod==3)?'rd':'th';
            formatString = formatString.replace("#YYYY#",YYYY).replace("#YY#",YY).replace("#MMMM#",MMMM).replace("#MMM#",MMM).replace("#MM#",MM).replace("#M#",M).replace("#DDDD#",DDDD).replace("#DDD#",DDD).replace("#DD#",DD).replace("#D#",D).replace("#th#",th);
            h=(hhh=this.getHours());
            if (h==0) h=24;
            if (h>12) h-=12;
            hh = h<10?('0'+h):h;
            hhhh = hhh<10?('0'+hhh):hhh;
            AMPM=(ampm=hhh<12?'am':'pm').toUpperCase();
            mm=(m=this.getMinutes())<10?('0'+m):m;
            ss=(s=this.getSeconds())<10?('0'+s):s;

            return formatString.replace("#hhhh#",hhhh).replace("#hhh#",hhh).replace("#hh#",hh).replace("#h#",h).replace("#mm#",mm).replace("#m#",m).replace("#ss#",ss).replace("#s#",s).replace("#ampm#",ampm).replace("#AMPM#",AMPM);
            // token:     description:             example:
            // #YYYY#     4-digit year             1999
            // #YY#       2-digit year             99
            // #MMMM#     full month name          February
            // #MMM#      3-letter month name      Feb
            // #MM#       2-digit month number     02
            // #M#        month number             2
            // #DDDD#     full weekday name        Wednesday
            // #DDD#      3-letter weekday name    Wed
            // #DD#       2-digit day number       09
            // #D#        day number               9
            // #th#       day ordinal suffix       nd
            // #hhhh#     2-digit 24-based hour    17
            // #hhh#      military/24-based hour   17
            // #hh#       2-digit hour             05
            // #h#        hour                     5
            // #mm#       2-digit minute           07
            // #m#        minute                   7
            // #ss#       2-digit second           09
            // #s#        second                   9
            // #ampm#     "am" or "pm"             pm
            // #AMPM#     "AM" or "PM"             PM
        };

    });
})( jQuery );
