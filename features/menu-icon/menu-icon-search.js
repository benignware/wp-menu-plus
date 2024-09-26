/* globals global */
jQuery(function($){
	var searchRequest;

  [...document.querySelectorAll('.menu-plus-icon-input-wrapper')].forEach(wrapper => {
    const icon = wrapper.querySelector('.menu-plus-icon-input-icon');
    const input = wrapper.querySelector('.menu-plus-icon-input');

    input.addEventListener('blur', event => {
      event.target.value = event.target.dataset.value;
      event.target.style.color = '';
    });

    input.addEventListener('input', event => {
      const { value, dataset: { value: originalValue }} = event.target;
      
      event.target.style.color = value !== originalValue ? '#595959' : '';
    });

    input.addEventListener('keypress', event => {
      if (event.keyCode === 13) {
        event.preventDefault();
      }
    });

    const clear = wrapper.querySelector('.menu-plus-icon-input-clear');

    clear.addEventListener('click', event => {
      input.value = '';
      input.dataset.value = '';
    });

    $(input).autoComplete({
      minChars: 2,
      source: function(term, suggest) {
        try { searchRequest.abort(); } catch(e){}
        searchRequest = $.get(global.ajax, { search: term, action: 'agnosticon_search', dataType: "json" }, function(res) {
          suggest(res.data);
        });
      },
      renderItem: function (item, search) {
        // escape special characters
        search = search.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
        var re = new RegExp("(" + search.split(' ').join('|') + ")", "gi");
        
        return `<li class="autocomplete-suggestion" style="list-style: none; display: flex" data-val="${item.id}"><span class="menu-plus-icon-suggestion-icon" style="display: inline-flex; margin-right: 4px; font-size: 18px; min-width: 20px; align-items-center; ${item.style}">${item.entity}</span>${item.id.replace(re, "<b>$1</b>")}</li>`;
      },
      onSelect: function (event, value, item) {
        input.dataset.value = value;
        const selectedIcon = item.get(0).querySelector('.menu-plus-icon-suggestion-icon');

        if (selectedIcon) {
          icon.style.fontFamily = selectedIcon.style.fontFamily;
          icon.style.fontWeight = selectedIcon.style.fontWeight;
          icon.textContent = selectedIcon.textContent;
          input.style.color = '';
          input.classList.remove('is-invalid');
        }
      }
    })
  });
});