    var url = "api.php"

    var mode = "serverList";


    var pivot_coordinate = {
        'x': 0,
        'y': 0,
        'z': 0,
        'raw': ''
    };

    var server;

    var coords_obj_old = null;
    var tobj_old = null;
    var intervalId;
    var categories_old = null;
    var owners_old = null;

    var titleFirstTime_flag = true;

    var upTableText_temp = '';

    var focus_blur_pivot_tmp;

    document.addEventListener("DOMContentLoaded", function() {
        robbeh();
        intervalId = setInterval(robbeh, 5000);
    });


    document.getElementById('pivot').onkeyup = function() {
        var pivot_coords_tmp = validate_rawGps('pivot');
        if (pivot_coords_tmp) {
            pivot_coordinate = pivot_coords_tmp;
        }
    }


    function robbeh() {

        //Thx to http://youmightnotneedjquery.com/
        var request = new XMLHttpRequest();
        if (mode === "serverList") {
            request.open('GET', url + '?operation=get_serverList', true);
        } else if (mode === "server_coords") {
            request.open('GET', url + '?operation=get_serverCoords&server_id=' + server, true);
        }

        request.onload = function() {
            if (request.status >= 200 && request.status < 400) {
                // Yohoo! Success!
                var data = JSON.parse(request.responseText);

                var username = document.getElementById('username');
                username.innerHTML = data["user"]["name"] + "<br />Disconnnetti"

                if (mode === "serverList") {
                    var tobj = data["items"]["servers_list"];
                    if (tobj_old === null) {
                        tobj_old = tobj;
                    }
                    if (titleFirstTime_flag) {
                        document.getElementById('title_big').innerHTML = data["items"]["settings"]["title_big_home"];
                        document.getElementById('title_small').innerHTML = data["items"]["settings"]["title_small_home"];
                        document.getElementById('title_big_status_icon').innerHTML = "";
                        document.getElementById('title_small_players').innerHTML = "";
                        document.getElementById('title_small_status').innerHTML = "";
                        document.getElementById('general_info').innerHTML = data["items"]["settings"]["description_home"];

                        document.getElementById('upTableText').innerHTML = 'Prossimo aggiornamento in <span id="time">05:00</span> minuti.';
                        document.getElementById('form_server').innerHTML = '<input id="address_form" type="text" name="address" placeholder="indirizzo IP o DNS">\
        <input id="port_form" type="text" name="port" placeholder="porta">\
        <input id="name_form" type="text" name="name" placeholder="nome">\
        <input id="description_form" type="text" name="description" placeholder="descrizione">\
        <input type="button" id="btn_id" value="Aggiungi" onclick="add_server()" />';

                        titleFirstTime_flag = false;
                    }

                    // Build the table
                    build_table(1, tobj, "universalTable");

                    tobj_old = tobj;
                } else if (mode === "server_coords") {

                    var coords_obj = data["items"]["server_coords"];
                    if (coords_obj_old === null) {
                        coords_obj_old = coords_obj;
                    }

                    if (titleFirstTime_flag) {
                        document.getElementById('title_big').innerHTML = data["items"]["server_coords"]["server"]["name"];
                        document.getElementById('title_small').innerHTML = data["items"]["server_coords"]["server"]["address"] + ":" + data["items"]["server_coords"]["server"]["port"];
                        document.getElementById('general_info').innerHTML = data["items"]["server_coords"]["server"]["description"];

                        document.getElementById('upTableText').innerHTML = '<a href="#" onclick="goto_serverList()" />Indietro</a>';
                        document.getElementById('form_server').innerHTML = '<input type="hidden" id="server_id_form" name="server_id" value="' + server + '"><input id="raw_form" type="text" name="raw" placeholder="coordinate">\
<select id="categories_form" name="categories" size="4"><option>Attendere...</option></select>\
<select id="owner_form" name="owner" size="1"><option>Attendere...</option></select>\
<input type="button" id="btn_id" value="Aggiungi" onclick="add_coordinates()" />';
                        document.getElementById("categories_form").multiple = true;

                        document.getElementById('raw_form').onkeyup = function() {
                            validate_rawGps('raw_form')
                        };


                        titleFirstTime_flag = false;
                    }

                    document.getElementById('title_big_status_icon').innerHTML = " ●";
                    document.getElementById('title_small_players').innerHTML = " - 12/16 ";
                    document.getElementById('title_small_status').innerHTML = "- Online";

                    // Build the table
                    build_table(1, coords_obj, "universalTable");
                    coords_obj_old = coords_obj;

                    //Finish to compile forms and updates them only when necessary
                    build_form(data);

                }

            } else {
                // We reached our target server, but it returned an error
            }
        };
        request.onerror = function() {
            // There was a connection error of some sort
        };

        request.send();
    };



    function build_table(head, body, idTable) {

        if (mode === "serverList") {
            var table = document.getElementById(idTable);
            var thead = "<thead><tr><th>Nome</th><th>Giocatori</th><th>Ping</th><th>IP:porta</th></tr></thead>";
            var tbody = "<tbody>";

            for (var i = 0; i < Object.keys(body).length; i++) {
                var tr = '<tr class="invalid">';
                if (i < Object.keys(tobj_old).length) {
                    if (tobj_old[i]["server_id"] === body[i]["server_id"]) {
                        var tr = '<tr>';
                    }
                }
                tr += '<td><a href="#" onclick="goto_serverCoords(' + body[i].server_id.toString() + ')">' + body[i].name + '</a></td><td><a href="#" onclick="goto_serverCoords(' + body[i].server_id.toString() + ')">' + body[i].players.toString() + '/' + body[i].maxPlayers.toString() + '</a></td><td><a href="#" onclick="goto_serverCoords(' + body[i].server_id.toString() + ')">' + body[i].ping.toString() + 'ms</a></td><td><a id="r' + i + '" href="#" onclick="copyToClipboard(\'r' + i + '\')">' + body[i].address.toString() + ":" + body[i].port.toString() + '</a></td></tr>';
                tbody += tr;
            }

            tbody += "</tbody>";
            table.innerHTML = thead + tbody;
        } else if (mode === "serverList_modify") {


            var table = document.getElementById(idTable);
            var thead = "<thead><tr><th>🚮</th><th>Nome</th><th>Giocatori</th><th>Ping</th><th>IP:porta</th></tr></thead>";
            var tbody = "<tbody>";

            for (var i = 0; i < Object.keys(body).length; i++) {
                var tr = '<tr>';
                tr += '<td><input type="checkbox" value="' + body[i].server_id + '" /></td><td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + body[i].name + '</a></td>' + '<td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + body[i].players.toString() + '/' + body[i].maxPlayers.toString() + '</a></td><td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + body[i].ping.toString() + 'ms</a></td><td><a id="r' + i + '" href="#" onclick="copyToClipboard(\'r' + i + '\')">' + body[i].address.toString() + ":" + body[i].port.toString() + '</a></td></tr>';
                tbody += tr;
            }

            tbody += "</tbody>";
            table.innerHTML = thead + tbody;

        } else if (mode == "server_coords") {

            var table = document.getElementById(idTable);
            var thead = "<thead><tr><th>Nome</th><th>Distanza</th><th>Aggiunte da</th><th>Categoria</th><th>Raw</th></tr></thead>";
            var tbody = "<tbody>";

            for (var i = 0; i < Object.keys(body["coords"]).length; i++) {
                var tr = '<tr class="invalid">';
                if (i < Object.keys(coords_obj_old["coords"]).length) {
                    if (coords_obj_old["coords"][i]["coordinate_id"] === body["coords"][i]["coordinate_id"]) {
                        var tr = '<tr>';
                    }
                }
                var coords_attr_temp = get_coords_attr(body["coords"][i].raw);
                tr += '<td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + coords_attr_temp.name + '</a></td><td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + metersToHuman(coords_attr_temp.distance) + '</a></td><td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + body["coords"][i].owner_name.toString() + '</a></td><td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + categories_toString(body["coords"][i].categories) + '</a></td><td><a id="r' + i + '" href="#" onclick="copyToClipboard(\'r' + i + '\')">' + body["coords"][i].raw.toString() + '</a></td></tr>';
                tbody += tr;
            }

            tbody += "</tbody>";
            table.innerHTML = thead + tbody;


        } else if (mode === "server_coords_modify") {


            var table = document.getElementById(idTable);
            var thead = "<thead><tr><th>🚮</th><th>Nome</th><th>Distanza</th><th>Aggiunte da</th><th>Categoria</th><th>Raw</th></tr></thead>";
            var tbody = "<tbody>";

            for (var i = 0; i < Object.keys(body["coords"]).length; i++) {
                var tr = '<tr>';
                var coords_attr_temp = get_coords_attr(body["coords"][i].raw);
                tr += '<td><input type="checkbox" value="' + body["coords"][i].coordinate_id + '" /></td><td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + coords_attr_temp.name + '</a></td><td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + metersToHuman(coords_attr_temp.distance) + '</a></td><td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + body["coords"][i].owner_name.toString() + '</a></td><td><a href="#" onclick="copyToClipboard(\'r' + i + '\')">' + categories_toString(body["coords"][i].categories) + '</a></td><td><a id="r' + i + '" href="#" onclick="copyToClipboard(\'r' + i + '\')">' + body["coords"][i].raw.toString() + '</a></td></tr>';
                tbody += tr;
            }

            tbody += "</tbody>";
            table.innerHTML = thead + tbody;
        }




    }




    function build_form(data) {

        var options = "";
        var isChanged_flag = false;
        var isFirst_flag = true;
        if (categories_old === null) {
            isChanged_flag = true;
        }
        for (var i = 0; i < Object.keys(data["items"]["categories"]).length; i++) {
            if (isFirst_flag) {
                options = options + '<option value="' + data["items"]["categories"][i].id.toString() + '" selected="selected">' + data["items"]["categories"][i].name.toString() + '</option>';
                isFirst_flag = false;
            } else {
                options = options + '<option value="' + data["items"]["categories"][i].id.toString() + '">' + data["items"]["categories"][i].name.toString() + '</option>';
            }
            if (!isChanged_flag) {
                if (i < Object.keys(categories_old).length) {
                    if (categories_old[i]["id"] !== data["items"]["categories"][i]["id"]) {
                        isChanged_flag = true;
                    }
                }
            }
        }
        if (isChanged_flag) {
            categories_old = data["items"]["categories"];
            document.getElementById('categories_form').innerHTML = options;
        }



        options = "";
        isChanged_flag = false;
        isFirst_flag = true;
        if (owners_old === null) {
            isChanged_flag = true;
        }
        for (var i = 0; i < Object.keys(data["items"]["owners"]).length; i++) {
            if (isFirst_flag) {
                options = options + '<option value="' + data["items"]["owners"][i].id.toString() + '" selected="selected">' + data["items"]["owners"][i].name.toString() + '</option>';
                isFirst_flag = false;
            } else {
                options = options + '<option value="' + data["items"]["owners"][i].id.toString() + '">' + data["items"]["owners"][i].name.toString() + '</option>';
            }
            if (!isChanged_flag) {
                if (i < Object.keys(owners_old).length) {
                    if (owners_old[i]["id"] !== data["items"]["owners"][i]["id"]) {
                        isChanged_flag = true;
                    }
                }
            }
        }
        if (isChanged_flag) {
            owners_old = data["items"]["owners"];
            document.getElementById('owner_form').innerHTML = options;
        }

    }




    function add_server() {
        var data = {
            'operation': 'add_server',
            'address': document.getElementById("address_form").value,
            'port': document.getElementById("port_form").value,
            'name': document.getElementById("name_form").value,
            'description': document.getElementById("description_form").value
        }
        var params = encodeQueryData(data);
        sendPOST(params);
        document.getElementById("form_server").reset();
    }


    function add_coordinates() {

        var categoriesObj = document.getElementById("categories_form");
        var categories = [];
        for (i = 0; i < categoriesObj.options.length; i++) {
            if (categoriesObj.options[i].selected) {
                categories.push(categoriesObj.options[i].value);
            }
        }


        var raw = document.getElementById('raw_form').value.match(/^GPS:([^:]+):([-]?[0-9.]+):([-]?[0-9.]+):([-]?[0-9.]+):$/);

        if (raw) {




            var data = {
                'operation': 'add_coordinates',
                'raw': raw[0],
                'categories': categories,
                'owner_id': document.getElementById("owner_form").value,
                'server_id': document.getElementById("server_id_form").value
            }
            var params = encodeQueryData(data);
            sendPOST(params);
            document.getElementById("form_server").reset();



        } else {
            alert("Alcuni campi non sono stati compilati in maniera corretta!");
        }


    }



    function sendPOST(params) {


        var http = new XMLHttpRequest();

        http.open("POST", url, true);

        //Send the proper header information along with the request
        http.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

        http.onreadystatechange = function() { //Call a function when the state changes.
            if (http.readyState == 4 && http.status == 200) {
                //alert(http.responseText);
            }
        }
        http.send(params);
    }



    function encodeQueryData(data) {
        let ret = [];
        for (let d in data)
            if (Array.isArray(data[d])) {

                data[d].forEach(function(element, indice) {
                    ret.push(encodeURIComponent(d) + '[]=' + encodeURIComponent(element));
                });

            } else {
                ret.push(encodeURIComponent(d) + '=' + encodeURIComponent(data[d]));
            }
        console.log(ret.join('&'));
        return ret.join('&');
    }




    //Thx internet! <3
    function copyToClipboard(elementId) {

        // Create an auxiliary hidden input
        var aux = document.createElement("input");

        // Get the text from the element passed into the input
        aux.setAttribute("value", document.getElementById(elementId).innerHTML);

        // Append the aux input to the body
        document.body.appendChild(aux);

        // Highlight the content
        aux.select();

        // Execute the copy command
        document.execCommand("copy");

        // Remove the input from the body
        document.body.removeChild(aux);


    }

    function log() {
        console.log('---');
    }




    // Ritorna un array con i valori dei checkbox selezionati
    function getSelectedChbox() {
        var array = []; // Array che conterrà i valori dei checkbox selezionati

        // ottiene tutti i tag "input" ed il loro numero
        var inpfields = document.getElementsByTagName('input');
        var nr_inpfields = inpfields.length;

        for (var i = 0; i < nr_inpfields; i++) {
            //solo i tag "input" che sono checkbox e sono selezionati
            if (inpfields[i].type == 'checkbox' && inpfields[i].checked == true) {
                array.push(inpfields[i].value);
            }
        }
        if (mode === "server_coords_modify") {
            return {
                "operation": "del_coordinates",
                "coordinate_id": array
            };
        } else if (mode === "serverList_modify") {
            return {
                "operation": "del_server",
                "server_id": array
            };
        }
    }




    // Quando si clicca sul pulsante con tag #modButton, viene lanciato un alert con i valori selezionati nelle checkbox
    function modifyMode() {

        var delButton = document.getElementById('replaceButtons');
        delButton.innerHTML = '<input type="button" value="Esci modalità modifica" id="modButtonExit" onclick="modifyModeExit()" />';
        upTableText_temp = document.getElementById('upTableText').innerHTML;
        document.getElementById('upTableText').innerHTML = '';

        //Ferma l'intervallo
        clearInterval(intervalId);
        intervalId = false;

        if (mode === "server_coords") {
            mode = "server_coords_modify";
            //Costruisce la tabella
            build_table(1, coords_obj_old, "universalTable");
        } else if (mode === "serverList") {
            mode = "serverList_modify";
            //Costruisce la tabella
            build_table(1, tobj_old, "universalTable");
        }


    }



    // Quando si clicca sul pulsante con tag #modButtonExit, viene lanciato un alert con i valori selezionati nelle checkbox
    function modifyModeExit() {

        var delButton = document.getElementById('replaceButtons');

        //Elimina le righe selezionate
        var data = getSelectedChbox();
        var params = encodeQueryData(data);
        sendPOST(params);

        delButton.innerHTML = '<input type="button" value="Modifica" id="modButton" onclick="modifyMode()" />';
        document.getElementById('upTableText').innerHTML = upTableText_temp;

        if (mode === "server_coords_modify") {
            mode = "server_coords";
        } else if (mode === "serverList_modify") {
            mode = "serverList";
        }


        //Soluzione alternativa meno birikina
        robbeh();
        if (!intervalId) {
            console.log("goto_serveList: " + intervalId);
            intervalId = setInterval(robbeh, 5000);
        }

    }


    function goto_serverCoords(server_id) {



        coords_obj_old = null;

        categories_old = null;
        owners_old = null;



        server = server_id;
        mode = "server_coords";
        titleFirstTime_flag = true;


        //Soluzione alternativa meno birikina
        robbeh();
        if (!intervalId) {
            console.log("goto_serveList: " + intervalId);
            intervalId = setInterval(robbeh, 5000);
        }

    }


    function goto_serverList() {


        document.getElementById('upTableText').innerHTML = 'Prossimo aggiornamento in <span id="time">05:00</span> minuti.';


        mode = "serverList";

        titleFirstTime_flag = true;

        //Soluzione alternativa meno birikina
        robbeh();
        if (!intervalId) {
            console.log("goto_serveList: " + intervalId);
            intervalId = setInterval(robbeh, 5000);
        }

    }




    function get_coords_attr(raw) {

        raw = raw.match(/^GPS:([^:]+):([-]?[0-9.]+):([-]?[0-9.]+):([-]?[0-9.]+):$/);



        return {
            'name': raw[1],
            'distance': distanceVector({
                'x': raw[2],
                'y': raw[3],
                'z': raw[4]
            }, pivot_coordinate)
        };
    }



    function distanceVector(v1, v2) {
        var dx = v1.x - v2.x;
        var dy = v1.y - v2.y;
        var dz = v1.z - v2.z;

        return Math.sqrt(dx * dx + dy * dy + dz * dz);
    }


    function metersToHuman(distance) {
        if (distance >= 1000) {
            var num = distance / 1000;
            return num.toFixed(2) + "Km";
        } else if (distance >= 1) {
            return distance.toFixed(2) + "m";
        } else {
            var num = distance * 100;
            return num.toFixed(0) + "cm";
        }
    }


    function categories_toString(cat_array) {
        var cat_string = '';
        for (var i = 0; i < cat_array.length; i++) {
            if (i === cat_array.length - 1) {
                cat_string = cat_string + cat_array;
            } else {
                cat_string = cat_string + cat_array + ", ";
            }
        }
        return cat_string;
    }




    function validate_rawGps(element_id) {
        var element = document.getElementById(element_id);
        var value;
        if (element.value === undefined) {
            value = element.innerHTML;
        } else {
            value = element.value;
        }
        var raw_pivot = value.match(/^GPS:([^:]+):([-]?[0-9.]+):([-]?[0-9.]+):([-]?[0-9.]+):$/);
        if (raw_pivot) {
            document.getElementById(element_id).style.backgroundColor = "#d2ff6f";
            return {
                'raw': raw_pivot[0],
                'name': raw_pivot[1],
                'x': raw_pivot[2],
                'y': raw_pivot[3],
                'z': raw_pivot[4]
            };
        } else {
            document.getElementById(element_id).style.backgroundColor = "#ff6f6f";
            if (value === "") {
                document.getElementById(element_id).style.backgroundColor = "transparent";
            }
            return false;
        }
    }



    function focus_pivot_div() {
        var element = document.getElementById('pivot');
        focus_blur_pivot_tmp = element.innerHTML;
        element.innerHTML = "";
    }

    function blur_pivot_div() {
        var element = document.getElementById('pivot');
        if (!validate_rawGps('pivot')) {
            element.innerHTML = focus_blur_pivot_tmp;
        }
    }
