document.addEventListener("DOMContentLoaded", function () {
    const months = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
    
    var auction = setInterval(function() {
        var d  = new Date();
        var n  = d.getTime();
        var dd = d.getDate("d");
        var mm = months[d.getMonth()];
        var yy = d.getFullYear();
        var fa = new Date(mm + " " + dd + ", " + yy + " 06:00:00").getTime();
        var ma = new Date(mm + " " + dd + ", " + yy + " 12:00:00").getTime();
        var sa = new Date(mm + " " + dd + ", " + yy + " 18:00:00").getTime();
        
        if(n < fa) {
            var dif = fa - n;
            var auc = false;
        } else if(n > fa && n < (fa + 3600000)) {
            var dif = 0;
            var auc = true;
        } else if(n > fa && n < ma) {
            var dif = ma - n;
            var auc = false;
        } else if(n > ma && n < (ma + 3600000)) {
            var dif = 0;
            var auc = true;
        } else if(n > ma && n < sa) {
            var dif = sa - n;
            var auc = false;
        } else if(n > sa && n < (sa + 3600000)) {
            var dif = 0;
            var auc = true;
        }else {
            var dif = 0;
            var auc =false;
        }

        var h = Math.floor(dif / (1000 * 60 * 60));
        var m = Math.floor((dif % (1000 * 60 * 60)) / (1000 * 60));
        var s = Math.floor((dif % (1000 * 60)) / 1000);

        var auction_counter = document.getElementById("auction");
        if( auction_counter ) {
            if(dif == 0) {
                if(auc) {
                    auction_counter.innerHTML = "The auction is now live. Refresh this page.";
                } else {
                    auction_counter.innerHTML = "The next auction will take tomorrow morning."
                }
            } else {
                if(h < 1) {
                    if(s == 0) {
                        auction_counter.innerHTML = "The next auction will take place in " + m + " minutes.";
                    } else if(m == 0) {
                        auction_counter.innerHTML = "The next auction will take place in " + s + " seconds.";
                    } else {
                        auction_counter.innerHTML = "The next auction will take place in " + m + " minutes and " + s + " seconds.";
                    }
                } else {
                    if(s == 0) {
                        auction_counter.innerHTML = "The next auction will take place in " + h + " hours and " + m + " minutes.";
                    } else if(m == 0) {
                        auction_counter.innerHTML = "The next auction will take place in " + h + " hours and " + s + " seconds.";
                    } else {
                        auction_counter.innerHTML = "The next auction will take place in " + h + " hours, " + m + " minutes and " + s + " seconds.";
                    }
                }
            }
            
        }
    }, 1000);

    hamburger = document.getElementsByClassName("hamburger");
    if(hamburger) {
        for(a = 0; a < hamburger.length; a++) {
            hamburger[a].addEventListener("click", function() {
                document.getElementById("content__navbar").classList.toggle("content__navbar--active");
            })
        }
    }

    rollback = document.getElementsByClassName("rollback");
    if(rollback) {
        for(a = 0; a < rollback.length; a++) {
            rollback[a].addEventListener("click", function() {
                document.getElementById("content__navbar").classList.toggle("content__navbar--active");
            })
        }
    }
});
