commit_group_info = function() {
  var group_name = $('#group_name').val();
  var alert_info =
    '如果点击提交,该小组的信息将添加到数据库中!!!' + '\n小组名称:' + group_name;
  if (window.confirm(alert_info)) {
    var post_data = { group_name: group_name, method: 'add_group' };
    $.ajax({
      type: 'POST',
      url: './manage_group.php',
      data: post_data,
      success: function(msg) {
        if (msg.match(/write_group_succ/g)) {
          if (document.getElementById('group_dis').style.display == 'block') {
            group_dis_display();
          }
          if (document.getElementById('group_del').style.display == 'block') {
            group_del_display();
          }
          alert('add group success');
        } else {
          alert('add group fail!!!');
        }
      }
    });
  }
};
commit_del_group_info = function() {
  var group_list = $('.group_list');
  check_del_group_list = [];
  check_del_group_name_list = [];
  for (var i = 0, len = group_list.length; i < len; i++) {
    if (group_list[i].checked) {
      check_del_group_list[i] = group_list[i]['id'];
      check_del_group_name_list[i] = group_list[i]['name'];
    }
  }
  var str_check_del_group_list = '';
  for (var i in check_del_group_list) {
    if (str_check_del_group_list != '') {
      str_check_del_group_list += ',';
    }
    str_check_del_group_list += check_del_group_list[i];
  }
  var str_check_del_group_name_list = '';
  for (var i in check_del_group_name_list) {
    if (str_check_del_group_name_list != '') {
      str_check_del_group_name_list += ',';
    }
    str_check_del_group_name_list += check_del_group_name_list[i];
  }
  var alert_info =
    '如果点击提交,该小组的信息从数据库中删除!!!' +
    '\n小组名称:' +
    str_check_del_group_name_list +
    '\n小组id:' +
    str_check_del_group_list;
  if (window.confirm(alert_info)) {
    var post_data = {
      del_group_id: str_check_del_group_list,
      method: 'del_group'
    };
    console.log(post_data);
    $.ajax({
      type: 'POST',
      url: './manage_group.php',
      data: post_data,
      success: function(msg) {
        if (msg.match(/del_group_succ/g)) {
          if (document.getElementById('group_dis').style.display == 'block') {
            group_dis_display();
          }
          if (document.getElementById('group_del').style.display == 'block') {
            group_del_display();
          }
          alert('del group success');
        } else {
          alert('del group fail!!!');
        }
      }
    });
  }
};
get_all_group = function(id, init_text) {
  var post_data = { method: 'get_all_group' };
  $.ajax({
    type: 'POST',
    url: './manage_group.php',
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
      all_append = '';
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
              if (id == '#dis_all_group') {
                temp = temp + '<tr><td>' + json_data[i][keys[j]] + '</td></tr>';
              } else {
                temp =
                  temp +
                  'name:&nbsp&nbsp' +
                  json_data[i][keys[j]] +
                  '&nbsp&nbsp&nbsp';
              }
            }
          }
        }
        if (id == '#dis_all_group') {
          var ele = temp;
        } else {
          if (id == '#get_all_group') {
            var ele =
              temp +
              '<input type="checkbox" class=group_list' +
              ' id=' +
              groupid +
              ' name=' +
              groupname +
              ' /><br/>';
          }
        }
        all_append += ele;
      }
      if (id == '#dis_all_group') {
        all_append =
          '<table class="gridtable"><tr><th>name</th></tr><tr>' +
          all_append +
          '</tr></table>';
      }
      $(id).append(all_append);
      return len;
    }
  });
};
group_del_display = function() {
  document.getElementById('group_del').style.display = 'block';
  var init_text = '请选中要删除的小组   (notice:可多选) :';
  window.onload = get_all_group('#get_all_group', init_text);
};
group_dis_display = function() {
  document.getElementById('group_dis').style.display = 'block';
  var init_text = '小组列表:';
  window.onload = get_all_group('#dis_all_group', init_text);
};
add_commit = function() {
  window.onload = commit_group_info(); 
};
del_commit = function() {
  window.onload = commit_del_group_info(); 
};
