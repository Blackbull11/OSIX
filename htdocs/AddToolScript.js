    // Fermer la popup en cliquant sur la croix, pas de nécessité de distinction

function Close_tool_popup() {
    if (window.confirm('Attention vous perdrez toutes les modifications effectuées !')) {
        console.log('Display.none');
        document.getElementById("popup-tool").style.display = "none";
        const to_defilter_elts = document.getElementsByClassName("to_defilter");
        Array.from(to_defilter_elts).forEach((element) => {
            element.style.filter = "revert-layer";
        });
    }
 }

