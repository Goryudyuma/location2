var NearStations = React.createClass({displayName: "NearStations",
	render: function(){
		var nearstation = this.props.data.map(function(station){
			return(
				React.DOM.tr({},
					React.DOM.td({},station.stn),
					React.DOM.td({},station.linename),
					React.DOM.td({},station.opc),
					React.DOM.td({},station.distance)
				)
			);
		});
		
		var thead = React.DOM.thead({},
			React.DOM.tr({},
				React.DOM.th({},'駅名'),
				React.DOM.th({},'線名'),
				React.DOM.th({},'社名'),
				React.DOM.th({},'距離')
			)
		);
		
		return (
			React.DOM.table({},[thead,nearstation])
		);
	}
});

var divleft = {
	float: 'left'
};

var Stations = React.createClass({displayName: "Stations",
	getInitialState: function(){
		return {
			data: [],
			point: {x: -1.0 , y: -1.0}
		};
	},
	loadNearStationsFromServer: function(){
		if(this.state.point !== this.props.point){
			$.ajax({
				url: this.props.url,
				type: 'POST',
				dataType: 'json',
				data: {x: this.props.point.x , y: this.props.point.y},
				cache: false,
				success: function(data){
					this.replaceState({data: data , point: this.props.point});
				}.bind(this),
				error: function(xhr, status, err){
					console.error(this.props.url, status, err.toString());	
				}.bind(this),
			});
		}
	},
	componentDidMount: function(){
		this.loadNearStationsFromServer();
	},
	render: function(){
		if(this.state.data.toString() !== [].toString() ){
			this.loadNearStationsFromServer();
			return(
				React.createElement("div", {className: "Stations", style: divleft}, 
					React.createElement("h1", null, "最寄り駅リスト"), 
					React.createElement(NearStations, {data: this.state.data})
				)
			);
		}else{
			return(
				React.createElement("div", {className: "Stations", style: divleft}, 
					"駅情報取得中"
				)
			);
		}
	}
});

var Nearpoints = React.createClass({displayName: "Nearpoints",
	render: function(){
		var nearstation = this.props.data.map(function(station){
			return(
				React.DOM.tr({},
					React.DOM.td({},station.sectionid),
					React.DOM.td({},station.linename),
					React.DOM.td({},station.opc),
					React.DOM.td({},station.dist)
				)
			);
		});
		
		var thead = React.DOM.thead({},
			React.DOM.tr({},
				React.DOM.th({},'ID'),
				React.DOM.th({},'線名'),
				React.DOM.th({},'社名'),
				React.DOM.th({},'距離')
			)
		);
		
		return (
			React.DOM.table({},[thead,nearstation])
		);
	}
});

var Nearpoint = React.createClass({displayName: "Nearpoint",
	getInitialState: function(){
		return {
			data: [],
			point: {x: -1.0 , y: -1.0}
		};
	},
	loadNearPointFromServer: function(){
		if(this.state.point !== this.props.point){
			$.ajax({
				url: this.props.url,
				type: 'POST',
				dataType: 'json',
				data: {x: this.props.point.x , y: this.props.point.y},
				cache: false,
				success: function(data){
					this.replaceState({data: data , point: this.props.point});
				}.bind(this),
				error: function(xhr, status, err){
					console.error(this.props.url, status, err.toString());	
				}.bind(this),
			});
		}	
	},
	componentDidMount: function(){
		this.loadNearPointFromServer();
	},
	render: function(){
		if(this.state.data.toString() !== [].toString() ){
			this.loadNearPointFromServer();
			return(
				React.createElement("div", {className: "Nearpoint", style: divleft}, 
					React.createElement("h1", null, "最寄り地点リスト"), 
					React.createElement(Nearpoints, {data: this.state.data})
				)
			);
		} else {
			return(
				React.createElement("div", {className: "Nearpoint", style: divleft}, 
					"近くの線路を取得中"
				)
			);
		}	
	}	
});

var Location = React.createClass({displayName: "Location",
	getInitialState: function(){
		return {
			point:{x: -1.0 , y: -1.0}
		};
	},
	nowLocation: function(){
		var options = {
			enableHighAccuracy: true,
			timeout: 3600000,
			maximumAge: 0
		};

		function error(err) {
			console.error('ERROR(' + err.code + '): ' + err.message);
		};

		navigator.geolocation.watchPosition(
			(pos) => {
				if (this.state.point.x !== pos.coords.latitude || this.state.point.y !== pos.coords.longitude){
					this.replaceState({point: {x: pos.coords.latitude , y: pos.coords.longitude}});
				}
			}, error, options
		);
	},
	componentDidMount: function(){
		this.nowLocation();
	},
	render: function(){
		if(this.state.point.x !== -1.0 && this.state.point.y !== -1.0){
			return(
				React.createElement("div", {className: "Location"}, 
					React.createElement(Stations, {url: "stations.php", point: this.state.point}), 
					React.createElement(Nearpoint, {url: "points.php", point: this.state.point})
				)
			);
		} else {
			return(
				React.createElement("div", {className: "Location"}, 
					"GPS取得中"
				)
			);
		}
	}
});

ReactDOM.render(
	React.createElement(Location, null),
	document.getElementById('content')
);
