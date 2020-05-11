Ajaxreq = function(re_type, host, post_data, re_async) {
  var result = '';
  $.ajax({
    type: re_type,
    url: host,
    data: post_data,
    async: re_async,
    success: function(msg) {
      result = msg;
    }
  });
  return result;
};
replace_id2name = function(id_string) {
  var post_data = { method: 'replace_id2name', id2name_string: id_string };
  var result = Ajaxreq('POST', './manage_personnel.php', post_data, false);
  if (result != '') {
    var str_arr = result.match(/id2namestr-->(.+)<--/);
    var temp = str_arr[1];
  }
  return JSON.parse(temp);
};
Setval = function() {
  var belong_to_group = $('#modify_personnel').val();
  $('#txta1').text('该名人员信息 : ' + belong_to_group);
  belong_to_group = belong_to_group.match(/Belong_to_group:(.*)/);
  if (belong_to_group != null) {
    belong_to_group = belong_to_group[1].trim();
    var array_ = belong_to_group.split(',');
    var group_list = $('.modify_group_list');
    for (var i = 0, len = group_list.length; i < len; i++) {
      if (array_.indexOf(group_list[i]['name']) > -1) {
        group_list[i]['checked'] = 'checked';
      } else {
        group_list[i]['checked'] = false;
      }
    }
  }
};
commit_personnel_info = function() {
  var group_list = $('.group_list');
  check_group_list = [];
  check_group_name_list = [];
  for (var i = 0, len = group_list.length; i < len; i++) {
    if (group_list[i].checked) {
      check_group_list[i] = group_list[i]['id'];
      check_group_name_list[i] = group_list[i]['name'];
    }
  }
  var str_check_group_list = '';
  for (var i in check_group_list) {
    if (str_check_group_list != '') {
      str_check_group_list += ',';
    }
    str_check_group_list += check_group_list[i];
  }
  var str_check_group_name_list = '';
  for (var i in check_group_name_list) {
    if (str_check_group_name_list != '') {
      str_check_group_name_list += ',';
    }
    str_check_group_name_list += check_group_name_list[i];
  }
  console.log(str_check_group_list);
  console.log(str_check_group_name_list);
  var personnel_name = $('#personnel_name').val();
  var alert_info =
    '如果点击提交,该人员的信息将添加到数据库中!!!' +
    '\n人员名称:' +
    personnel_name +
    '\n所在小组:' +
    str_check_group_name_list;
  if (window.confirm(alert_info)) {
    var post_data = {
      personnel_name: personnel_name,
      personnel_group_id: str_check_group_list,
      method: 'add_personnel'
    };
    $.ajax({
      type: 'POST',
      url: './manage_personnel.php',
      data: post_data,
      success: function(msg) {
        if (msg.match(/write_personnel_succ/g)) {
          if (document.getElementById('personnel_modify').style.display == 'block') {
            personnel_modify_display();
          }
          if (document.getElementById('personnel_del').style.display == 'block') {
            personnel_del_display();
          }
          if (document.getElementById('personnel_dis').style.display == 'block') {
            personnel_dis_display();
          }
          alert('add personnel success');
        } else {
          alert('add personnel fail!!!');
        }
      }
    });
  }
};
commit_modify_personnel_info = function() {
  var group_list = $('.modify_group_list');
  check_group_list = [];
  check_group_name_list = [];
  for (var i = 0, len = group_list.length; i < len; i++) {
    if (group_list[i].checked) {
      check_group_list[i] = group_list[i]['id'];
      check_group_name_list[i] = group_list[i]['name'];
    }
  }
  var str_check_group_list = '';
  for (var i in check_group_list) {
    if (str_check_group_list != '') {
      str_check_group_list += ',';
    }
    str_check_group_list += check_group_list[i];
  }
  var str_check_group_name_list = '';
  for (var i in check_group_name_list) {
    if (str_check_group_name_list != '') {
      str_check_group_name_list += ',';
    }
    str_check_group_name_list += check_group_name_list[i];
  }
  console.log(str_check_group_list);
  console.log(str_check_group_name_list);
  var personnel_name = $('#modify_personnel')
    .find('option:selected')
    .text();
  var init_group = $('#modify_personnel')
    .find('option:selected')
    .val();
  console.log(personnel_name);
  var alert_info =
    '如果点击提交,该人员的信息将更新到数据库中!!!' +
    '\n人员名称:' +
    personnel_name +
    '\n所在小组:' +
    str_check_group_name_list +
    '\n初始信息:' +
    init_group;
  if (window.confirm(alert_info)) {
    var post_data = {
      personnel_name: personnel_name,
      personnel_group_id: str_check_group_list,
      method: 'modify_personnel'
    };
    $.ajax({
      type: 'POST',
      url: './manage_personnel.php',
      data: post_data,
      success: function(msg) {
        console.log(msg);
        if (msg.match(/modify_personnel_succ/g)) {
          if (document.getElementById('personnel_modify').style.display == 'block') {
            personnel_modify_display();
          }
          if (document.getElementById('personnel_del').style.display == 'block') {
            personnel_del_display();
          }
          if (document.getElementById('personnel_dis').style.display == 'block') {
            personnel_dis_display();
          }
          alert('modify personnel success');
        } else {
          alert('mofify personnel fail!!!');
        }
      }
    });
  }
};
commit_del_personnel_info = function() {
  var personnel_list = $('.personnel_list');
  check_del_personnel_list = [];
  check_del_personnel_name_list = [];
  for (var i = 0, len = personnel_list.length; i < len; i++) {
    if (personnel_list[i].checked) {
      check_del_personnel_list[i] = personnel_list[i]['id'];
      check_del_personnel_name_list[i] = personnel_list[i]['name'];
    }
  }
  var str_check_del_personnel_list = '';
  for (var i in check_del_personnel_list) {
    if (str_check_del_personnel_list != '') {
      str_check_del_personnel_list += ',';
    }
    str_check_del_personnel_list += check_del_personnel_list[i];
  }
  var str_check_del_personnel_name_list = '';
  for (var i in check_del_personnel_name_list) {
    if (str_check_del_personnel_name_list != '') {
      str_check_del_personnel_name_list += ',';
    }
    str_check_del_personnel_name_list += check_del_personnel_name_list[i];
  }
  var alert_info =
    '如果点击提交,该人员的信息从数据库中删除!!!' +
    '\n人员名称:' +
    str_check_del_personnel_name_list +
    '\n人员id:' +
    str_check_del_personnel_list;
  if (window.confirm(alert_info)) {
    var post_data = {
      del_personnel_id: str_check_del_personnel_list,
      method: 'del_personnel'
    };
    console.log(post_data);
    $.ajax({
      type: 'POST',
      url: './manage_personnel.php',
      data: post_data,
      success: function(msg) {
        if (msg.match(/del_personnel_succ/g)) {
          if (document.getElementById('personnel_modify').style.display == 'block') {
            personnel_modify_display();
          }
          if (document.getElementById('personnel_del').style.display == 'block') {
            personnel_del_display();
          }
          if (document.getElementById('personnel_dis').style.display == 'block') {
            personnel_dis_display();
          }
          alert('del personnel success');
        } else {
          alert('del personnel fail!!!');
        }
      }
    });
  }
};
get_all_group = function(id, init_text) {
  var post_data = { method: 'get_all_group' };
  $.ajax({
    type: 'POST',
    url: './manage_personnel.php',
    data: post_data,
    success: function(msg) {
      var json_data = msg.match(/json_data-->(.+)<--/);
      if (json_data == null) {
        $(id).text(init_text);
        $(id).append('get group fail');
      } else {
        $(id).text(init_text);
        $(id).append('<br>');
        json_data = JSON.parse(json_data[1]);
        len = json_data.length;
      }
      var jsonlen = json_data.length;
      for (var i = 0; i < jsonlen; i++) {
        if (json_data[i] == null) {
          break;
        }
        var keys = Object.keys(json_data[i]);
        var temp = '';
        for (var j = 0, len = keys.length; j < len; j++) {
          if (keys[j] == 'id') {
            var groupid = json_data[i][keys[j]];
          } else {
            if (keys[j] == 'name') {
              var groupname = json_data[i][keys[j]];
              temp = json_data[i][keys[j]];
            }
          }
        }
        if (id == '#modify_get_all_group') {
          var ele =
            temp +
            '&nbsp<input type="checkbox" class=modify_group_list' +
            ' id=' +
            groupid +
            ' name=' +
            groupname +
            ' /><br/>';
        } else {
          var ele =
            temp +
            '&nbsp<input type="checkbox" class=group_list' +
            ' id=' +
            groupid +
            ' name=' +
            groupname +
            ' /><br/>';
        }
        $(id).append(ele);
      }
      return len;
    }
  });
};
get_all_personnel = function(id, init_text) {
  var post_data = { method: 'get_all_personnel' };
  $.ajax({
    type: 'POST',
    url: './manage_personnel.php',
    data: post_data,
    success: function(msg) {
      var json_data = msg.match(/json_data-->(.+)<--/);
      if (json_data == null) {
        $(id).text(init_text);
        $(id).append('get personnel fail');
      } else {
        $(id).text(init_text);
        $(id).append('<br>');
        json_data = JSON.parse(json_data[1]);
        len = json_data.length;
      }
      var jsonlen = json_data.length;
      var group_id_string = get_special_list(json_data, 'group_id');
      var group_id_array = replace_id2name(id_string);
      all_append = '';
      for (var i = 0; i < jsonlen; i++) {
        if (json_data[i] == null) {
          break;
        }
        var keys = Object.keys(json_data[i]);
        var temp = '';
        for (var j = 0, len = keys.length; j < len; j++) {
          if (keys[j] == 'id') {
            var personnelid = json_data[i][keys[j]];
          } else {
            if (keys[j] == 'name') {
              var personnelname = json_data[i][keys[j]];
              if (id == '#dis_all_personnel') {
                temp = temp + '<tr><td>' + json_data[i][keys[j]] + '</td>';
              } else {
                temp =
                  temp +
                  'name:&nbsp&nbsp' +
                  json_data[i][keys[j]] +
                  '&nbsp&nbsp&nbsp';
              }
            } else {
              if (keys[j] == 'group_id') {
                var personnelgroupid = json_data[i][keys[j]];
                if (personnelgroupid != null) {
                  var gp_id = personnelgroupid.split(',');
                  var replace_gp_id = [];
                  for (var k in gp_id) {
                    replace_gp_id[k] = group_id_array[gp_id[k]];
                  }
                  var personnelgroupid = replace_gp_id.join(',');
                }
                if (id == '#dis_all_personnel') {
                  temp = temp + '<td>' + personnelgroupid + '</td></tr>';
                } else {
                  temp =
                    temp +
                    'belong to group:&nbsp&nbsp' +
                    personnelgroupid +
                    '&nbsp&nbsp&nbsp';
                }
              }
            }
          }
        }
        if (id == '#dis_all_personnel') {
          var ele = temp;
        } else {
          if (id == '#get_all_personnel') {
            var ele =
              temp +
              '<input type="checkbox" class=personnel_list' +
              ' id=' +
              personnelid +
              ' name=' +
              personnelname +
              ' group_id=' +
              personnelgroupid +
              ' /><br/>';
          } else {
            if (id == '#modify_personnel') {
              var ele =
                temp +
                '<option value="' +
                'Name:' +
                personnelname +
                ',Belong_to_group:' +
                personnelgroupid +
                '">' +
                personnelname +
                '</option>';
            }
          }
        }
        all_append += ele;
      }
      if (id == '#dis_all_personnel') {
        all_append =
          '<table class="gridtable"><tr><th>name</th><th>group</th></tr><tr>' +
          all_append +
          '</tr></table>';
      }
      $(id).append(all_append);
      return len;
    }
  });
};
get_special_list = function(json_data, type) {
  var temp = '';
  for (var i in json_data) {
    if (temp != '') {
      temp += ',';
    }
    temp += json_data[i][type];
  }
  var temp = temp.split(',');
  var temp = Array.from(new Set(temp));
  id_string = temp.join(',');
  return id_string;
};
personnel_add_display = function() {
  document.getElementById('personnel_add').style.display = 'block';
  var init_text = '该人员所在小组   (notice:可多选) :';
  window.onload = get_all_group('#get_all_group', init_text);
};
personnel_dis_display = function() {
  document.getElementById('personnel_dis').style.display = 'block';
  var init_text = '人员列表:';
  window.onload = get_all_personnel('#dis_all_personnel', init_text);
};
personnel_modify_display = function() {
  document.getElementById('personnel_modify').style.display = 'block';
  var init_text = '请选中要修改的人员  :';
  window.onload = get_all_personnel('#modify_personnel', init_text);
  var init_text = '该人员所在小组   (notice:可多选) :';
  window.onload = get_all_group('#modify_get_all_group', init_text);
};
personnel_del_display = function() {
  document.getElementById('personnel_del').style.display = 'block';
  var init_text = '请选中要删除的人员   (notice:可多选) :';
  window.onload = get_all_personnel('#get_all_personnel', init_text);
};
add_commit = function() {
  window.onload = commit_personnel_info(); 
};
del_commit = function() {
  window.onload = commit_del_personnel_info();  
};
modify_commit = function() {
  window.onload = commit_modify_personnel_info();
};
modify_personnel = function() {
  window.onload = Setval();
};
