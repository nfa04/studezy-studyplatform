const colors = ["#EA3E35","#EA6A37","#F5D221","#8DEF38","#26CB13","#31FDE5","#2353F3","#652FED","#A828F4","#E622A5","#EA3E35","#EA6A37","#F5D221","#8DEF38","#26CB13","#31FDE5","#2353F3","#652FED","#A828F4","#E622A5","#EA3E35","#EA6A37","#F5D221","#8DEF38","#26CB13","#31FDE5","#2353F3","#652FED","#A828F4","#E622A5"];
function arrayPercent(array) {
	var sum = arraySum(array);
	var i;
	for (i = 0; i < array.length; i++) {
		array[i] = array[i] / sum * 100;
	}
	return array;
}
function arraySum(array) {
	var i;
	var sum = 0;
	for (i = 0; i < array.length; i++) {
		sum = sum + array[i];
	}
	return sum;
}
function setShadow(mode,ctx,offsetY) {
	switch(mode) {
		case "no-shadow":
			ctx.shadowColor = "rgba(0,0,0,0)";
		break;
		case "light-shadow":
			ctx.shadowColor = "rgba(0,0,0,0.2)";
		break;
		case "shadow":
		default:
			ctx.shadowColor = "rgba(0,0,0,0.5)";
		break;
	}
	ctx.shadowOffsetX = 2;
	if(offsetY!=false) ctx.shadowOffsetY = 2; else ctx.shadowOffsetY = 0;
	ctx.shadowBlur = 5;
	return true;
}
function getMaxStrlen(array) {
	var strlen = array.map((x) => x.length);
	return Math.max(...strlen);
}
function graphBackground(canvas,data,names) {
	var ctx = canvas.getContext("2d");
	setShadow("no-shadow",ctx);
	ctx.clearRect(0,0,canvas.width,canvas.height);
	ctx.fillStyle = "#DBD9D9";
	ctx.fillRect(0,canvas.height-40,canvas.width,1);
	ctx.fillRect(Math.max(...data).toString().length * 8 + 8,10,canvas.width,1);
	ctx.fillRect((Math.max(...data) * 0.25).toString().length * 8 + 8, Math.round((canvas.height - 50) / 4) + 10,canvas.width,1);
	ctx.fillRect((Math.max(...data) * 0.5).toString().length * 8 + 8, Math.round(2 * ((canvas.height - 50) / 4)) + 10,canvas.width,1);
	ctx.fillRect((Math.max(...data) * 0.25).toString().length * 8 + 8, Math.round(3 * ((canvas.height - 50) / 4)) + 10,canvas.width,1);
	ctx.font = "15px Arial";
	ctx.fillStyle = "grey";
	ctx.fillText(Math.max(...data),5,15);
	ctx.fillText((Math.max(...data) * 0.75),5,(canvas.height - 50) / 4 + 5);
	ctx.fillText((Math.max(...data) * 0.5),5,2 * ((canvas.height - 50) / 4) + 5);
	ctx.fillText((Math.max(...data) * 0.25),5,3 * ((canvas.height - 50) / 4) + 5);
	var i;
	for (i = 0; i < names.length; i++) { 
		ctx.fillStyle = "#DBD9D9";
		ctx.fillRect((i + 1) * (canvas.width / (names.length + 1)),10,1,canvas.height-60);
		ctx.fillStyle = "grey";
		ctx.fillText(names[i],(i + 1) * (canvas.width / (names.length + 1)) - (names[i].length * 8 / 2),canvas.height - 20);
	}
}
class VisualizedDataset {

    data;
    canvas;

    constructor(data, canvas) {
        this.data = data;
        this.canvas = canvas;
    }

	setFillStyle(fillStyle) {
		this.canvas.getContext("2d").fillStyle = fillStyle;
	}

    piechart(offsetX, offsetY, size) {
        var names = Object.keys(this.data);
        var data = Object.values(this.data);
        var ctx = this.canvas.getContext("2d");
        ctx.clearRect(0,0,this.canvas.width,this.canvas.height);
        var percents = arrayPercent(data);
        var tillNow = 1.5;
        var i;
		var maxStrLen = getMaxStrlen(names);
        for (i = 0; i < percents.length; i++) {
            setShadow("light-shadow", ctx);
            ctx.fillStyle = colors[i];
            ctx.beginPath();
            ctx.moveTo(offsetX,offsetY);
            ctx.arc(offsetX,offsetY,size,tillNow*Math.PI,(2/100*percents[i])*Math.PI+tillNow*Math.PI);
            tillNow += 2/100*percents[i];
            ctx.lineTo(offsetX,offsetY);
            ctx.fill();
            ctx.font = "15px Arial";
            ctx.fillText(names[i],this.canvas.width - (10 * maxStrLen),i*20+(this.canvas.height / 2)-(percents.length * 10));
            ctx.fillRect(this.canvas.width - (10 * maxStrLen) -15,i*20+(this.canvas.height / 2)-(percents.length * 10) - 10,10,10);
        }
    }

    barchart() {
        var data = Object.values(this.data);
        var names = Object.keys(this.data);
        var ctx = this.canvas.getContext("2d");
		setShadow("no-shadow",ctx);
		ctx.clearRect(0,0,this.canvas.width,this.canvas.height);
		graphBackground(this.canvas,data,names);
		setShadow("shadow",ctx,false);
		for (var i = 0; i < data.length; i++) {
		    ctx.fillRect((i + 1) * (this.canvas.width / (names.length + 1)) - 25,(this.canvas.height - 50) - ((this.canvas.height - 50) / Math.max(...data) * data[i]) + 10,50,(this.canvas.height - 50) / Math.max(...data) * data[i]);
		}
		return true;
    }

	horizontalBarchart(maxValue = Math.max(...Object.values(this.data))) {
		var data = Object.values(this.data);
		var names = Object.keys(this.data);
		var ctx = this.canvas.getContext("2d");
		setShadow("light-shadow", ctx);
		ctx.clearRect(0,0,this.canvas.width,this.canvas.height);
		ctx.fillText("0", this.canvas.width * 0.25 - 10, this.canvas.height - 15);
		ctx.fillText(Math.round(maxValue / 2).toString(), this.canvas.width * 0.575 - 10, this.canvas.height - 15)
		ctx.fillText(Math.round(maxValue).toString(), this.canvas.width * 0.9, this.canvas.height - 15);
		for(var i = 0; i < data.length; i++) {
			var offsetY = (this.canvas.height - 25) / (data.length + 1) * (i + 1) + 25;
			ctx.fillText(names[i], 25, offsetY);
			ctx.fillRect(this.canvas.width * 0.25, offsetY - 15, data[i] / maxValue * (this.canvas.width * 0.65), 25);
		}
	}

}