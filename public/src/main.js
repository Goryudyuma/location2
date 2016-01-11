var Table = ReactBootstrap.Table;
var Input = ReactBootstrap.Input;
var Col = ReactBootstrap.Col;

var Top = React.createClass({
	render: function(){
		return(
			<div>
				<p>データは国土交通省国土政策局国土情報課
				<a href="http://nlftp.mlit.go.jp/ksj/index.html" target="_blank">国土数値情報ダウンロードサービス</a>の
				<a href="http://nlftp.mlit.go.jp/ksj/gml/datalist/KsjTmplt-N05.html" target="_blank">国土数値情報鉄道時系列データ</a>から取得しています。
				詳細は<a href="https://github.com/Goryudyuma/location2" target="_blank">https://github.com/Goryudyuma/location2</a>まで。</p>
			</div>
		);
	}
});

var Viewtable = React.createClass({
	render: function(){
		var oneofthetbody = this.props.data.map(function(one){
			return(
				<tbody>
					<tr onClick={this.props.chengelineid.bind(null, one.sectionid)} onTouchStart={this.props.chengelineid.bind(null, one.sectionid)}>
						<td>{this.props.stationflag === 1 ? one.stn : one.sectionid}</td>	
						<td>{one.linename}</td>
						<td>{one.opc}</td>
						<td>{Number(one.distance).toFixed(5)}</td>
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
			<Table striped bordered condensed hover className="Viewtable">
				{thead}
				{oneofthetbody}
			</Table>
		);
	}
});

var Nearpoint = React.createClass({
	getInitialState: function(){
		return {
			data: [],
			point: {x: -1.0 , y: -1.0},
			year: 9999
		};
	},
	loadNearPointFromServer: function(){
		if(this.state.point !== this.props.point || this.props.year !== this.state.year){
			$.ajax({
				url: this.props.url,
				type: 'POST',
				dataType: 'json',
				data: {x: this.props.point.x, y: this.props.point.y, year: this.props.year},
				cache: false,
				success: function(data){
					this.setState({data: data, point: this.props.point, year: this.props.year});
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
				<div className="Nearpoint" >
					<h1>最寄り{this.props.word}リスト</h1>
					<Viewtable data={this.state.data} chengelineid={this.props.chengelineid} stationflag={this.props.stationflag}/>
				</div>
			);
		} else {
			return(
				<div className="Nearpoint" >
					{this.props.word}情報取得中
				</div>
			);
		}	
	}	
});

var Selectyear = React.createClass({
	change: function(){
		this.props.changeyear(this.refs.year.getValue());
	},
	render: function(){
		return(
			<div>
					<Input type="number" ref="year" min={1950} max={2100} defaultValue={this.props.year} onChange={this.change} />
			</div>
		);
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
		this.drawmap();
	},
	componentDidUpdate: function(){
		this.drawmap();
	},
	shouldComponentUpdate: function(nextProps){
		return this.props.line !== nextProps.line || this.props.year !== nextProps.year;
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
					<Viewmap line={this.state.line[this.props.year][this.props.lineid]} point={this.props.point} year={this.props.year} style={mapstyle}/>
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
	changeyear: function(year){
		this.setState({year: year});
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
					<Top />
					<Col sm={12} md={5}>
						<Nearpoint url="stations.php" point={this.state.point} lineid={this.state.lineid} year={this.state.year} chengelineid={this.chengelineid} stationflag={1} word="駅" />
					</Col>
					<Col sm={12} md={5}>
						<Nearpoint url="points.php" point={this.state.point} lineid={this.state.lineid} year={this.state.year} chengelineid={this.chengelineid} stationflag={0} word="地点" />
					</Col>
					<Col sm={12} md={2}>
						<Selectyear year={this.state.year} changeyear={this.changeyear} />
					</Col>
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
