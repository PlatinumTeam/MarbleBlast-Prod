/**
 * Marks the active tab within the heading as active based on the page that is currently being viewed.
 * @param tab The tab we are currently on.
 */
function markActive(tab) {
    var listNodes = document.getElementById("navbarlist").children;

    for (var i = 0; i < listNodes.length; i++) {
        var node = listNodes[i];
        var aTag = node.children[0];

        // set the `tab` element active
        // UL -> LI -> A
        if (aTag.innerHTML.toLowerCase() == tab.toLowerCase()) {
            node.setAttribute("class", "active");
            break;
        }
    }
}