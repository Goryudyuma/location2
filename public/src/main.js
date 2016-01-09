var Viewtable = React.createClass({
	render: function(){
		var oneofthetbody = this.props.data.map(function(one){
			return(
				<tbody>
					<tr onClick={this.props.chengelineid.bind(null, one.sectionid)}>
						<td>{this.props.stationflag === 1 ? one.stn : one.sectionid}</td>	
						<td>{one.linename}</td>
						<td>{one.opc}</td>
						<td>{one.distance}</td>
					</tr>
				</tbody>
			);	
		}.bind(this));

		var thead = [''].map(function(){
			return(
				<thead>
					<tr>
						<th>{this.props.stationflag === 1 ? '駅名' : 'ID'}</th>
						<th>{'線名'}</th>
						<th>{'社名'}</th>
						<th>{'距離'}</th>
					</tr>
				</thead>
			);	
		}.bind(this));

		return(
			<table className="Viewtable">
				{thead}
				{oneofthetbody}
			</table>
		);
	}
});

var divleft = {
	float: 'left'
};

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
				data: {x: this.props.point.x, y: this.props.point.y, year: this.props.year},
				cache: false,
				success: function(data){
					this.setState({data: data , point: this.props.point});
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
					<h1>最寄り{this.props.word}リスト</h1>
					<Viewtable data={this.state.data} chengelineid={this.props.chengelineid} stationflag={this.props.stationflag}/>
				</div>
			);
		} else {
			return(
				<div className="Nearpoint" style={divleft}>
					{this.props.word}情報取得中
				</div>
			);
		}	
	}	
});

var mapstyle = {
	width: "100%",
	height: "100%"
};

var Viewmap = React.createClass({
	getInitialState: function(){
		return { 
		}
	},
	drawmap: function(){
		var latlng = new google.maps.LatLng(this.props.point.x, this.props.point.y);
		var mapOptions = {
			zoom:9,
			center:latlng,
			mapTypeId:google.maps.MapTypeId.ROADMAP
		};
		var map = new google.maps.Map(ReactDOM.findDOMNode(this.refs.googlemap), mapOptions);
		map.data.addGeoJson(this.props.line);	
		map.data.setStyle({
		    strokeWeight: 5,
			strokeColor: 'blue',
		  });
		var marker = new google.maps.Marker({
			position: latlng,
			map: map
		});
	},
	componentDidMount: function(){
		google.maps.event.addDomListener(window, "load", this.drawmap);
		this.drawmap();
	},
	componentDidUpdate: function(){
		this.drawmap();
	},
	shouldComponentUpdate: function(nextProps){
		return this.props.line !== nextProps.line;
	},
	render: function(){
		return(
			<div className="Viewmap" style={mapstyle}>
				<div ref="googlemap" style={mapstyle}></div>
			</div>
		);
	}
});

var Linemap = React.createClass({
	getInitialState: function(){
		return {
			line:{}	
		};
	},
	getlinedata: function(){
		if (this.props.lineid !== -1 && (this.state.line[this.props.year] == null || this.state.line[this.props.year][this.props.lineid] == null)) {
			$.ajax({
				url: this.props.url,
				type: 'POST',
				dataType: 'json',
				data: {id: this.props.lineid, year: this.props.year},
				cache: true,
				success: function(data){
					var nowstate = this.state.line;
					if(nowstate[this.props.year] == null){
						nowstate[this.props.year] = {};
					}
					nowstate[this.props.year][this.props.lineid] = data;
					this.setState({line: nowstate});
				}.bind(this),
				error: function(xhr, status, err){
					console.error(this.props.url, status, err.toString());	
				}.bind(this),
				});
		}	
	},
	render: function(){
		if (this.props.lineid === -1 || this.props.lineid == null) {
			return (
				<div className="Linemap">
					路線を選んでください
				</div>
			);
		} else if (this.state.line[this.props.year] != null && this.state.line[this.props.year][this.props.lineid] != null) {
			return (
				<div className="Linemap">
					<Viewmap line={this.state.line[this.props.year][this.props.lineid]} point={this.props.point} style={mapstyle}/>
				</div>
			);
		} else {
			this.getlinedata(this.props.lineid);
			return(
				<div className="Linemap">
					線データを取得中
				</div>
			);	
		}
	}
});

var Location = React.createClass({
	getInitialState: function(){
		var now = new Date();
		return {
			point: {x: -1.0 , y: -1.0},
			lineid: -1,
			year: now.getFullYear()
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
					this.setState({point: {x: pos.coords.latitude , y: pos.coords.longitude}});
				}
			}, error, options
		);
	},
	chengelineid: function(lineid){
		this.setState({lineid: lineid});
	},
	componentDidMount: function(){
		this.nowLocation();
	},
	render: function(){
		if(this.state.point.x !== -1.0 && this.state.point.y !== -1.0){
			return(
				<div className="Location" style={mapstyle}>
					<Nearpoint url="stations.php" point={this.state.point} lineid={this.state.lineid} year={this.state.year} chengelineid={this.chengelineid} stationflag={1} word="駅" />
					<Nearpoint url="points.php" point={this.state.point} lineid={this.state.lineid} year={this.state.year} chengelineid={this.chengelineid} stationflag={0} word="地点" />
					<Linemap url="json.php" point={this.state.point} lineid={this.state.lineid} year={this.state.year} style={mapstyle}/>
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
