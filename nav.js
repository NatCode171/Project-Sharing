const navBar = document.getElementsByTagName("nav")[0]
const mobileNavTopBar = document.getElementsByClassName("mobile-nav")[0]
let navOpen = false
mobileNavTopBar.getElementsByTagName("svg")[0].addEventListener("click",()=>{
    document.documentElement.style.setProperty('--topbar-height', `${mobileNavTopBar.offsetHeight}px`);
    navOpen = !navOpen
    if (navOpen)
        navBar.style.transform = "translateX(0%)"
    else
        navBar.style.transform = ""
})