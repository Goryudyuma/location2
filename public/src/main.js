var NearStations = React.createClass({
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

var Stations = React.createClass({
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
				<div className="Stations" style={divleft}>
					<h1>最寄り駅リスト</h1>
					<NearStations data={this.state.data}/>
				</div>
			);
		}else{
			return(
				<div className="Stations" style={divleft}>
					駅情報取得中
				</div>
			);
		}
	}
});

var Nearpoints = React.createClass({
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

var Nearpoint = React.createClass({
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
				<div className="Nearpoint" style={divleft}>
					<h1>最寄り地点リスト</h1>
					<Nearpoints data={this.state.data}/>
				</div>
			);
		} else {
			return(
				<div className="Nearpoint" style={divleft}>
					近くの線路を取得中
				</div>
			);
		}	
	}	
});

var Location = React.createClass({
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
				<div className="Location">
					<Stations url="stations.php" point={this.state.point} />
					<Nearpoint url="points.php" point={this.state.point} />
				</div>
			);
		} else {
			return(
				<div className="Location">
					GPS取得中
				</div>
			);
		}
	}
});

ReactDOM.render(
	<Location />,
	document.getElementById('content')
);