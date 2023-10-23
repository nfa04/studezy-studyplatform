class MenuBar {

    node;
    itemList;

    constructor(node, itemList) {
        this.node = node;
        this.itemList = itemList;

        // Add event listeners
        for(var i = 0; i < node.children.length; i++) {
            node.children[i].addEventListener("click", this.pickItem.bind(this, i));
        }
        itemList.slice(1).forEach(element => {
            element.style.display = "none";
        });
    }

    pickItem(activateNr, event) {
        console.log(activateNr);
        for(var i = 0; i < this.itemList.length; i++) {
            this.itemList[i].style.display = "none";
            this.node.children[i].className = "";
        }
        this.itemList[activateNr].style.display = "block";
        this.node.children[activateNr].className = "menubar_selected";
    }

}