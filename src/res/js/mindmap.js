const svgNS = "http://www.w3.org/2000/svg";
const letterStandardWidth = 15;
const letterStandardHeight = 20;
const colorPalette = ["rgb(15, 163, 177)", "rgb(217, 229, 214)", "rgb(237, 222, 164)", "rgb(247, 160, 114)", "rgb(255, 155, 66)"];
const bubblePadding = 10;

const DELTA_MODES = {"DELTA_CREATE": 'c', "DELTA_UPDATE": 'u', "DELTA_DROP": 'd'};

class MindMap {
    
    mainNode;
    svgParent;
    svg;
    treeDisplayDiv;
    listViewSelected;

    constructor(mainNodeContent, svgParent = null, treeDiv = null) {
        this.mainNode = new MindBubble(mainNodeContent);
        this.svgParent = svgParent;
        this.treeDisplayDiv = treeDiv;
    }

    getMainNode() {
        return this.mainNode;
    }

    getTree() {
        var tree = {};
        tree[this.mainNode.getContent()] = {id: this.mainNode.getID(), c: {}};
        function loopAndAdd(node, pathPointer) {
            var children = node.getChildren();
            Object.keys(children).forEach(key => {
                pathPointer[children[key].getContent()] = {};
                pathPointer[children[key].getContent()]['c'] = {};
                pathPointer[children[key].getContent()]['id'] = children[key].getID();
                loopAndAdd(children[key], pathPointer[children[key].getContent()]['c']);
            });
        }
        loopAndAdd(this.mainNode, tree[this.mainNode.getContent()]['c']);
        return tree;
    }

    createFromTree(tree) {
        this.mainNode = new MindBubble(Object.keys(tree)[0], null, tree[Object.keys(tree)[0]]['id']);
        function loopAndAdd(node, pathPointer) {
            Object.keys(pathPointer['c']).forEach(key => {
                console.log(pathPointer);
                var child = node.addChild(key, pathPointer['c'][key]['id']);
                loopAndAdd(child, pathPointer['c'][key]);
            });
        }
        loopAndAdd(this.mainNode, tree[Object.keys(tree)[0]]);
    }

    calcTreeDepth() {
        var tree = this.getTree();
        
        // Function by ChatGPT
        function calculateDepth(obj) {
            if (typeof obj !== 'object' || obj === null) {
              // If the object is not an array or is null, return 0
              return 0;
            }
          
            let maxDepth = 0;
          
            for (let key in obj) {
              if (obj.hasOwnProperty(key)) {
                // Recursively calculate the depth of each value and keep track of the maximum depth
                const depth = calculateDepth(obj[key]);
                maxDepth = Math.max(maxDepth, depth);
              }
            }
          
            // Add 1 to the maximum depth to account for the current level
            return maxDepth + 1;
          }

          return calculateDepth(tree) - 1;
      }

    draw(svgParent = null) {
        if(svgParent !== null) this.svgParent = svgParent;
    
        // Create an svg element to draw on
        if(typeof this.svg == "undefined") this.svg = document.createElementNS(
            svgNS, "svg"
        );

        // Clear svg and list view
        this.svg.replaceChildren();
        this.treeDisplayDiv.replaceChildren();

        this.svg.setAttribute("width", "1000");
        this.svg.setAttribute("height", "500");
        this.svg.style.border = "1px solid black";
        var svgWidth = this.svg.width.baseVal.value;
        var svgHeight = this.svg.height.baseVal.value;

        var centralBubble = document.createElementNS(
            svgNS, "rect"
        );

        console.log(this.mainNode.getContent().length * letterStandardWidth);
        centralBubble.setAttribute("x", (svgWidth - (this.mainNode.getContent().length * letterStandardWidth)) / 2);
        centralBubble.setAttribute("y", (svgHeight - letterStandardHeight) / 2);
        centralBubble.setAttribute("width", this.mainNode.getContent().length * letterStandardWidth);
        centralBubble.setAttribute("height", letterStandardHeight + 20);
        centralBubble.setAttribute("fill", colorPalette[0]);
        this.svg.appendChild(centralBubble);

        var centralText = document.createElementNS(svgNS, "text");
        centralText.setAttribute("x", (svgWidth - this.mainNode.getContent().length * letterStandardWidth) / 2);
        centralText.setAttribute("y", svgHeight / 2 + 20);
        centralText.setAttribute("fill", invertRGB(colorPalette[0]));
        centralText.textContent = this.mainNode.getContent();
        this.svg.appendChild(centralText);

        this.listViewSelected = this.mainNode;

        // Create all the bubbles around
        var treeDepth = this.calcTreeDepth();
        var renderNodeChildren = (node, depthCounter = 1, referenceX = svgWidth / 2, referenceY = svgHeight / 2, parentNode = this.svg, listViewGroup = this.treeDisplayDiv) =>
        {
            //console.log(node, depthCounter, referenceX, referenceY);
            var children = node.getChildren();
            for(var i = 0; i < children.length; i++) {


                // Calculate location of the bubble using trigonometry placing them evenly in circles around the main bubble (taking into account tree depth)

                /* 
                
                EXPLANATION FORMULA:

                1. One full circle = 2PI, so the circle is split up evenly into n pieces (n is the amount of children the node has), while looping this number will be multiplied by i (the loop counter) to represent the location of each element on the "circle"
                2. By calculating cos and sin, we can calculate x and y coordinates on the circle. We take this number and divide by tree depth to account for several "rings", which may be required to display several layers
                3. Multiply this number by (treeDepth - depthCounter) * 0.75 to make sure more inner "rings" are bigger (0.75 is to leave some padding at the image borders)
                4. Multiply by svgWidth or svgHeight as returned number is between 0 and 1 to adapt it to image size
                5. Make sure location is adapted to the space it will take up
                6. Add referenceX and referenceY ("circle" center)

                */

                var bubbleX = ((Math.cos(2 * Math.PI / children.length * i)) * (svgWidth - 120) / depthCounter - children[i].getContent().length * letterStandardWidth) / 2 + referenceX;
                var bubbleY = ((Math.sin(2 * Math.PI / children.length * i)) * (svgHeight - 120) / depthCounter - letterStandardHeight) / 2 + referenceY;


                var text = document.createElementNS(svgNS, "text");
                var bubble = document.createElementNS(svgNS, "rect");
                var group = document.createElementNS(svgNS, "g");

                var processClick = (clickedNode, clickedGroup, tempDepthCounter, tempRefX, tempRefY) => {
                    // Get the bubble and its children on top by removing the group and rerendering this part of the tree
                    console.log(clickedNode);
                    clickedGroup.remove();
                    renderNodeChildren(clickedNode, tempDepthCounter , tempRefX, tempRefY);
                }

                var timer = setTimeout(() => 0, 1);
                var processDragging = (event) => {
                    clearTimeout(timer);
                    var boundingRect = event.target.getBoundingClientRect();
                    console.log(event.clientX, boundingRect.left);
                    timer = setTimeout(() => {
                        event.target.setAttribute("x", event.clientX);
                        event.target.setAttribute("y", event.clientY);   
                    }, 100);
                }
                
                // Draw connection line to parent element
                var line = document.createElementNS(svgNS, "line");
                line.setAttribute("x1", bubbleX);
                line.setAttribute("y1", bubbleY);
                line.setAttribute("x2", referenceX);
                line.setAttribute("y2", referenceY);
                line.setAttribute("stroke", changeRGBBrightness(colorPalette[depthCounter], -100));
                console.log(changeRGBBrightness(colorPalette[depthCounter], -100))
                parentNode.appendChild(line);

                // Create the bubble and set attributes

                bubble.setAttribute("x", bubbleX);
                bubble.setAttribute("y", bubbleY);
                bubble.setAttribute("width", children[i].getContent().length * letterStandardWidth + (2 * bubblePadding));
                bubble.setAttribute("height", letterStandardHeight + (2 * bubblePadding));
                bubble.setAttribute("fill", colorPalette[depthCounter]);
                //bubble.addEventListener("mouseup", processClick.bind(this, children[i], group, depthCounter, bubbleX, bubbleY));
                bubble.addEventListener("mousedown", processDragging);
                parentNode.appendChild(bubble);

                // Create the text               
                text.setAttribute("x", bubbleX + bubblePadding);
                text.setAttribute("y", bubbleY + letterStandardHeight + bubblePadding);
                text.setAttribute("fill", invertRGB(colorPalette[depthCounter]));
                text.textContent = children[i].getContent();
                text.addEventListener("click", processClick.bind(this, children[i], group));
                parentNode.appendChild(text);

                parentNode.insertBefore(group, parentNode.firstChild);

                // If enabled, show in list view
                var listGroup = null;
                if(listViewGroup != null) {
                    // Create the group container
                    listGroup = document.createElement("div");
                    // Create the item
                    var listDiv = document.createElement("div");
                    listDiv.innerText = children[i].getContent()
                    listGroup.appendChild(listDiv);
                    listViewGroup.appendChild(listGroup);

                    // Manage selected 
                    listDiv.addEventListener("click", function (child, event) {
                        // Set selected view to represent the selected node from the tree
                        this.listViewSelected = child;
                        for(i = 0; i < this.treeDisplayDiv.getElementsByTagName("*").length; i++) {
                            this.treeDisplayDiv.getElementsByTagName("*")[i].style.backgroundColor = "initial";
                        }
                        event.target.style.backgroundColor = "green";
                    }.bind(this, children[i]));
                }

                renderNodeChildren(children[i], depthCounter + 1, bubbleX, bubbleY, group, listGroup);

            }
        }
        renderNodeChildren(this.mainNode);

        this.svgParent.appendChild(this.svg);
    }

    addToSelectedNode(childContent) {
        var child = this.listViewSelected.addChild(childContent);
        this.draw();
        return new MindDelta(DELTA_MODES['DELTA_CREATE'], child);
    }

    removeSelectedNode() {
        this.listViewSelected.getParent().removeChild(this.listViewSelected.getContent());
        this.draw();
        return new MindDelta(DELTA_MODES['DELTA_DROP'], this.listViewSelected);
    }

    getNodeByID(id) {
        var foundNode;
        var checkNode = (node) => {
            if(node.getID() == id) foundNode = node;
            var children = node.getChildren();
            for(var i = 0; i < children.length; i++) {
                checkNode(children[i]);
            }
        }
        checkNode(this.mainNode);
        return foundNode;
    }

    applyDelta(delta) {
        switch(delta.getType()) {
            case DELTA_MODES['DELTA_CREATE']:
                var node = delta.getNode();
                node.getParent().addChild(node.getContent(), node.getID());
            break;
            case DELTA_MODES['DELTA_UPDATE']:
                delta.getNode().setContent(delta.getNewContent());
                break;
            case DELTA_MODES['DELTA_DROP']:
                delta.getNode().getParent().removeChild(delta.getNode().getContent());
                break;
        }
        this.draw();
    }

}

class MindBubble {

    parent;
    children;
    content;
    id;

    constructor(content, parent = null, id = generateNodeID()) {
        this.content = content;
        this.parent = parent;
        this.children = [];
        this.id = id;
    }

    addChild(childContent, id = generateNodeID()) {
        var b = new MindBubble(childContent, this, id);
        this.children.push(b);
        return b;
    }

    removeChild(childContent) {
        this.children = this.children.filter(child => child.getContent() != childContent);
    }

    getParent() {
        return this.parent;
    }

    getChildren() {
        return this.children;
    }

    getContent() {
        return this.content;
    }

    getPath() {
        var pathArray = [this.getContent()];
        var getParent = (child) => {
            var parent = child.getParent();
            if(parent !== null) {
                pathArray.push(parent.getContent());
                getParent(parent);
            }            
        }
        getParent(this);
        pathArray.reverse();
        return pathArray;
    }

    getID() {
        return this.id;
    }

    setContent(content) {
        this.content = content;
    }

    
}

class MindDelta {

    actionType;
    node;
    newContent; // This property will only be set in update mode

    constructor(type = null, node = null) {
        this.actionType = type;
        this.node = node;
    }

    getDeltaString() {
        switch(this.actionType) {
            case DELTA_MODES['DELTA_CREATE']:
                return JSON.stringify({
                    'c': {
                        id: this.node.getID(),
                        p: this.node.getParent().getID(),
                        c: this.node.getContent()
                    }
                });
                break;
            case DELTA_MODES['DELTA_DROP']:
                return JSON.stringify({
                    'd': {
                        id: this.node.getID()
                    }
                });
                break;
            case DELTA_MODES['DELTA_UPDATE']:
                return JSON.stringify({
                    'u': {
                        id: this.node.getID(),
                        c: this.node.getContent()
                    }
                });
                break;
        }
    }

    getType() {
        return this.actionType;
    }

    // Function will return the node affected by the delta (the one that is dropped or updated). for create actions it will return the new node. 
    // Note: when using update mode, this will return the old node (not yet updated), to get the new content run getNewContent()
    getNode() {
        return this.node;
        
    }

    createFromDeltaString(deltaString, mindmap) {
        var delta = JSON.parse(deltaString);
        this.actionType = Object.keys(delta)[0];
        if(this.actionType == DELTA_MODES['DELTA_DROP'] || this.actionType == DELTA_MODES['DELTA_UPDATE']) this.node = mindmap.getNodeByID(delta[this.actionType]['id']);
        else this.node = new MindBubble(delta[this.actionType]['c'], mindmap.getNodeByID(delta[this.actionType]['p']), delta[this.actionType]['id']);
        if(this.actionType == DELTA_MODES['DELTA_UPDATE']) this.newContent = delta[this.actionType]['c'];
    }

    // Available only in update mode
    getNewContent() {
        return this.newContent;
    }

}

function generateNodeID() {
    return Math.round(Math.random() * 10000);
}

// Function inverts rgb color
function invertRGB(rgbString) {
    var intString = rgbString.slice(4, -1);
    console.log(intString);
    var values = intString.split(", ");
    var mapped = values.map(c => parseInt(255) - c);
    return "rgb(" + mapped.join(", ") + ")";
}

function changeRGBBrightness(rgbString, brightnessDelta) {
    var intString = rgbString.slice(4, -1);
    console.log(intString);
    var values = intString.split(", ");
    var mapped = values.map(c => Math.max(0, Math.min(parseInt(c) + brightnessDelta, 255)));
    return "rgb(" + mapped.join(", ") + ")";
}