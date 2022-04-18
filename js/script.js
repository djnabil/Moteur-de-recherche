function toggle_show(id) {
	let d1= document.getElementById(id);
	if(getComputedStyle(d1).display != "none") {
		d1.style.display = "none";
	} else {
		d1.style.display = "block";
	}	   
}