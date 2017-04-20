window.onload = function () {
	
	var chart = new CanvasJS.Chart("budgetbar",
    {
      animationEnabled: true,
      axisX: {     
        interval: 0,
        minimum: 0,
        maximum: 0,
        indexLabelFontSize: 50
      },
      axisY: {     
        interval: 10000,
        minimum: 0,
        maximum: 105000,
        valueFormatString: "€ #,###,###",
        gridThickness: 0,
        gridColor: "#ffffff",
        tickLength: 3,
        indexLabelFontSize: 50
      },
      data: [
    	{        
        type: "stackedBar",
        name: "Verbruikt",
        showInLegend: "false",
        dataPoints: [
	        { x:1, y: 27000, color: "#cc33bb", toolTipContent: "€ 30.000 Spent" }
	        ]
    	},
        {        
        type: "stackedBar",
        name: "Prognose V",
        showInLegend: "false",
        dataPoints: [
	        { x:1, y: 500, color: "#fc9935", toolTipContent: "€ 27.500 Prognose V" }
	        ]
    	},
        {        
        type: "stackedBar",
        name: "Verbruikt",
        showInLegend: "false",
        dataPoints: [
	        { x:1, y: 2500, color: "#cc33bb", toolTipContent: "€ 30.000 Spent" }
	        ]
    	},
    	{        
        type: "stackedBar",
        name: "Budget",
        showInLegend: "false",
        dataPoints: [
	        { x:1, y: 64500, color: "#EEEEEE", toolTipContent: "€ 100.000 Budget" }
	        ]
    	},
    	{
    	type: "stackedBar",
        name: "Prognose T",
        showInLegend: "false",
        dataPoints: [
	        { x:1, y: 500, color: "#999900", toolTipContent: "€ 95.000 Prognose" }
	        ]
    	},
    	{        
        type: "stackedBar",
        name: "Budget",
        showInLegend: "false",
        dataPoints: [
	        { x:1, y: 5000, color: "#EEEEEE", toolTipContent: "€ 100.000 Budget" }
	        ]
    	}
      ]
      ,
      legend:{
        cursor:"pointer",
        itemclick:function(e) {
          if(typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible){
            e.dataSeries.visible = false;
          }
          else {
            e.dataSeries.visible = true;
          }
          
          chart.render();
        }
      }
    });

    chart.render();


    var chart = new CanvasJS.Chart("planning-doughnut",
	{
		animationEnabled: true,
		data: [
		{        
			type: "doughnut",
			startAngle:-90,
			toolTipContent: "{label}: {y} - <strong>#percent%</strong>",
			indexLabel: "{label} #percent%",
			dataPoints: [
				{  y: 82, label: "Volgens plan", color: "#009966" },
				{  y: 2, label: "Werkelijk", color: "#bce4d7" },
				{  y: 16, label: "", color: "#ffffff"}
			]
		}
		]
	});
	chart.render();


	var chart = new CanvasJS.Chart("pie1",
	{
		animationEnabled: true,
		legend: {
			verticalAlign: "bottom",
			horizontalAlign: "center"
		},
		theme: "theme1",
		data: [
		{        
			type: "pie",
			indexLabelFontFamily: "Garamond",       
			indexLabelFontSize: 20,
			indexLabelFontWeight: "bold",
			startAngle:0,
			indexLabelFontColor: "MistyRose",       
			indexLabelLineColor: "darkgrey", 
			indexLabelPlacement: "inside", 
			toolTipContent: "{name}: {y}hrs",
			showInLegend: true,
			indexLabel: "#percent%", 
			dataPoints: [
				{  y: 52, name: "Time At Work", legendMarkerType: "triangle"},
				{  y: 44, name: "Time At Home", legendMarkerType: "square"},
				{  y: 12, name: "Time Spent Out", legendMarkerType: "circle"}
			]
		}
		]
	});
	chart.render();


	var chart = new CanvasJS.Chart("pie2",
	{
		animationEnabled: true,
		legend:{
			verticalAlign: "bottom",
			horizontalAlign: "center"
		},
		data: [
		{        
			indexLabelFontSize: 20,
			indexLabelFontFamily: "Monospace",       
			indexLabelFontColor: "darkgrey", 
			indexLabelLineColor: "darkgrey",        
			indexLabelPlacement: "outside",
			type: "pie",       
			showInLegend: true,
			toolTipContent: "{y} - <strong>#percent%</strong>",
			dataPoints: [
				{  y: 4181563, legendText:"PS 3", indexLabel: "PlayStation 3" },
				{  y: 2175498, legendText:"Wii", indexLabel: "Wii" },
				{  y: 3125844, legendText:"360",exploded: true, indexLabel: "Xbox 360" },
				{  y: 1176121, legendText:"DS" , indexLabel: "Nintendo DS"},
				{  y: 1727161, legendText:"PSP", indexLabel: "PSP" },
				{  y: 4303364, legendText:"3DS" , indexLabel: "Nintendo 3DS"},
				{  y: 1717786, legendText:"Vita" , indexLabel: "PS Vita"}
			]
		}
		]
	});
	chart.render();


	var chart = new CanvasJS.Chart("pie3",
	{
		animationEnabled: true,
		data: [
		{        
			type: "doughnut",
			startAngle:20,
			toolTipContent: "{label}: {y} - <strong>#percent%</strong>",
			indexLabel: "{label} #percent%",
			dataPoints: [
				{  y: 67, label: "Inbox" },
				{  y: 28, label: "Archives" },
				{  y: 10, label: "Labels" },
				{  y: 7,  label: "Drafts"},
				{  y: 4,  label: "Trash"}
			]
		}
		]
	});
	chart.render();



	var chart = new CanvasJS.Chart("burndown",
	{

		animationEnabled: true,
		axisX:{
			gridColor: "Silver",
			tickColor: "silver",
			interval: 1,
	        minimum: 0,
	        maximum: 10
		},                        
        toolTip:{
        	shared:true
        },
		theme: "theme2",
		axisY: {
			gridColor: "Silver",
			tickColor: "silver",
			interval: 50,
	        minimum: 0,
	        maximum: 300
		},
		legend:{
			verticalAlign: "center",
			horizontalAlign: "right"
		},
		data: [
		{        
			type: "line",
			showInLegend: true,
			lineThickness: 3,
			name: "Planned",
			color: "#3366ff",
			dataPoints: [
			{ x: 0, y: 250 },
			{ x: 1, y: 225 },
			{ x: 2, y: 200 },
			{ x: 3, y: 175 },
			{ x: 4, y: 150 },
			{ x: 5, y: 125 },
			{ x: 6, y: 100 },
			{ x: 7, y: 75 },
			{ x: 8, y: 50 },
			{ x: 9, y: 25 },
			{ x: 10, y: 0 }
			]
		},
		{        
			type: "line",
			showInLegend: true,
			name: "Actual",
			color: "#ff6600",
			lineThickness: 5,

			dataPoints: [
			{ x: 0, y: 250 },
			{ x: 1, y: 230 },
			{ x: 2, y: 205 },
			{ x: 3, y: 180 },
			{ x: 4, y: 145 },
			{ x: 5, y: 130 },
			{ x: 6, y: 120 },
			{ x: 7, y: 110 }
			]
		},
		{        
			type: "column",
			showInLegend: true,
			name: "Daily completed",
			color: "#808080",

			dataPoints: [
			{ x: 0, y: 0 },
			{ x: 1, y: 20 },
			{ x: 2, y: 25 },
			{ x: 3, y: 25 },
			{ x: 4, y: 35 },
			{ x: 5, y: 15 },
			{ x: 6, y: 10 },
			{ x: 7, y: 10 },
			{ x: 8, y: 0 },
			{ x: 9, y: 0 },
			{ x: 10, y: 0 }
			]
		}

		
		],
      legend:{
        cursor:"pointer",
        itemclick:function(e){
          if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
          	e.dataSeries.visible = false;
          }
          else{
            e.dataSeries.visible = true;
          }
          chart.render();
        }
      }
	});

chart.render();
}