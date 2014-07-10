$(function() {
  var interfaceUrl = $('#my_courses_interface').attr('href');
  var $container = $('#my_courses_dynamic');

  var saveSortsFor = function(type, $ul) {
    var url = $('#my_courses_sort').text();
    var regex = new RegExp(type + "id=([\\w-]{1,25})");

    var ids = [];
    var sortorder = [];

    $ul.children().each(function(index, elem) {
      var href = $(elem).find('.item_visibility').attr('href');
      ids.push(regex.exec(href)[1]);
      sortorder.push(index);
    });

    var params = {
      type: type,
      ids: ids.join(","),
      sortorder: sortorder.join(","),
    };

    return $.ajax({
      url: url,
      data: params,
      type: "POST",
    });
  };

  var sortableObject = {
    cursor: "move",
    handle: ".handle",
    axis: "y",
    placeholder: "placeholder",
    update: function(event, $ui) {
      var $ul = $ui.item.parent();
      var type = $ui.item.attr('class').match(/category/) ? 'category' : 'course';
      saveSortsFor(type, $ul);
    }
  };

  var categorySwitcher = function() {
    var $this = $(this);
    var $list = $this.siblings('.my_courses_list');
    var collapsed = $this.hasClass('minus');

    if (collapsed) {
      var toAdd = 'plus';
      var toRemove = 'minus';
      var listAdd = 'collapsed';
      var listRemove = 'not_collapsed';
    } else {
      var toAdd = 'minus';
      var toRemove = 'plus';
      var listAdd = 'not_collapsed';
      var listRemove = 'collapsed';
    }

    $this.removeClass(toRemove).addClass(toAdd);
    $list.removeClass(listRemove).addClass(listAdd);
    $this.html($('#my_courses_' + toAdd).html());

    $.ajax({
      url: $this.attr('href'),
      type: "POST",
    });

    return false;
  };

  var itemVisibility = function() {
    var $this = $(this);
    var $parent = $this.parent();
    var $list = $this.siblings('.my_courses_list');
    var hidden = $this.hasClass('inconspicuous');

    if (hidden) {
      var toAdd = 'visible';
      var toRemove = 'inconspicuous';
    } else {
      var toAdd = 'inconspicuous';
      var toRemove = 'visible';
    }

    $this.removeClass(toRemove).addClass(toAdd);
    $list.removeClass(toRemove).addClass(toAdd);
    $parent.removeClass(toRemove).addClass(toAdd);

    $this.html($('#my_courses_' + toAdd).html());

    $.ajax({
      url: $this.attr('href'),
      type: "POST",
    });

    return false;
  };
  
  var itemFavorite = function() {
    var $this = $(this);

    $.ajax({
      url: $this.attr('href'),
      type: "POST",
    }).done(function() {
        //refresh editing area
        createInterface(true);
    });

    return false;
  };
  
  var createInterface = function(editing) {
    $.ajax({
      url: interfaceUrl,
      data: { editing: editing ? 1 : 0 },
    }).done(function(html) {
      $container.html(html);

      if (editing) {
        $container.addClass('editing');
      } else {
        $container.removeClass('editing');
      }

      $('.course-sortable').sortable(sortableObject);
      $('.category_switcher').click(categorySwitcher);
      $('.item_favorite').click(itemFavorite);
      $('.item_visibility').click(itemVisibility);
    });
  };

  createInterface();

  $('#my_courses_interface').click(function() {
    $container.html($('<span/>').addClass('interface').text('Loading...'));

    createInterface(!$container.hasClass('editing'));
    return false;
  });
});
