/**
 *
 */
({
    events: {
    },
    mapOptions: {
        zoom: 13,
        address_fields: ['address','location']
    },
    _init: function () {
        var self = this;
        if(typeof google != "undefined" && typeof google.load == 'function') {
            google.load("maps", "3", {
                other_params:'sensor=false',
                callback: function(){
                    self.apiLoaded = true;
                    self.getData();
                }
            });
        } else {

            $.ajax({
                url: 'https://www.google.com/jsapi',
                dataType: 'script',
                success: function () {
                    self._init();
                }
            });
        }
    },

    getData: function() {
        var self = this;
        var address;
        //Load configure meta from modules/{Module}/metadata/base/views/googlemap.php
        //Otherwise it loads the mapOption variables as default
        for(var key in this.meta) {
            if(self.mapOptions[key]) {
                self.mapOptions[key] = self.meta[key];
            }
        }
        // loop through possible address fields
        for (var key in self.mapOptions.address_fields)
        {
            // if array of fields (street, city, state, zip),
            // piece fields together into an address
            if(self.mapOptions.address_fields[key] instanceof Array)
            {
                var addr_part;
                address = [];
                for (var addr_key in self.mapOptions.address_fields[key])
                {
                    addr_part = this.model.get(self.mapOptions.address_fields[key][addr_key]);
                    // skip empty fields
                    if(addr_part)
                    {
                        address.push(addr_part);
                    }
                }
                // join together parts with CSV string
                address = address.join(', ');
            }
            else
            {
                // no array, just use as field name
                address = this.model.get(self.mapOptions.address_fields[key]);
            }
            // if we found a valid address, we are done
            if(address)
                break;
        }
        if(address) {
            if(this.apiLoaded) {
                // geocode the address into lat/lon, render
                this.geocoder = this.geocoder || new google.maps.Geocoder();
                this.geocoder.geocode({
                    'address': address
                }, function(results, status) {
                    if (status == google.maps.GeocoderStatus.OK) {
                        self.renderHtml(results);
                    }
                });
            } else {
                this._init();
            }
        }
    },

    findLocalHour: function(l){
        var off = Math.round(Math.abs(l) * 24 / 360);
        var time = new Date();
        var hours = time.getUTCHours();

        //add or subtract based on west/east of prime meridian
        hours = hours + (off*(l/Math.abs(l)));

        if (hours > 23){
            hours = hours - 24;
        }
        var month = time.getMonth();
        //daylight saving time adjustments.
        if (month < 11 && month > 2){
            if (month == 3 && time.getDate() >= 14){
                hours++;
            }
            else if (month>3){
                hours++;
            }
        }
        return hours;
    },

    renderHtml: function(results) {
        var self = this;
        //find hours of local time based on longitude
        var localHour = self.findLocalHour(results[0].geometry.location.ab);
        var time = new Date();
        var localMinutes = time.getMinutes();
        var ampm = 'AM';

        if (localHour > 12){
            localHour = localHour-12;
            ampm = 'PM';
        }

        if (localHour < 0){
            localHour = localHour + 12;
            ampm = 'PM';
        }

        if (localMinutes < 10){
            localMinutes = '0' + localMinutes;
        }
        var dateString = localHour + ":" + localMinutes + " " + ampm;



        this.$('#map_time').html("<h2 align=left>" + dateString + "</h2>" );




        this.$(".maps-widget .title").text(results[0].formatted_address);
        this.$('#map_panel').show();
        if(this.map) {
            this.map.setCenter(results[0].geometry.location);
        } else {
            this.mapOptions['center'] = results[0].geometry.location;
            this.mapOptions['mapTypeId'] =  this.mapOptions.mapTypeId || google.maps.MapTypeId.ROADMAP;

            this.map = new google.maps.Map(this.$("#map_canvas")[0], this.mapOptions);
        }
        var marker = new google.maps.Marker({
            map: this.map,
            position: results[0].geometry.location
        });
    },



    bindDataChange: function() {
        var self = this;
        if (this.model) {
            this.model.on("change", function() {
                self.getData();
            }, this);
        }
    }

})
