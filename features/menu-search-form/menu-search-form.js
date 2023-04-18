(() => {
  const IS_COLLAPSED = 'is-collapsed';

  if (window.wpMenuSearchForm) {
    return
  }

  class WPMenuSearchForm {
    constructor() {
      this.handleClick = this.handleClick.bind(this);
      this.handleState = this.handleState.bind(this);

      window.addEventListener('click', this.handleClick);
    }

    static getOptions(searchForm) {
      return JSON.parse(atob(searchForm.getAttribute('data-menu-search-form'))); 
    }

    handleClick(event) {
      const searchForm = event.target.closest('*[data-menu-search-form]');
      const submitTarget = event.target.closest('button, a');
      const isSubmit = !!submitTarget;
      const isSubmitLink = submitTarget && submitTarget.nodeName.toLowerCase() === 'a';

      if (searchForm) {
        const { expandable } = WPMenuSearchForm.getOptions(searchForm);
        const input = searchForm.querySelector('input[name=s]');

        if (expandable) {
          const isCollapsed = searchForm.classList.contains(IS_COLLAPSED);

          if (isCollapsed) {
            input.focus();
            event.preventDefault();
            event.stopImmediatePropagation();
            searchForm.classList.remove(IS_COLLAPSED);
          } else if (input.value && isSubmit && isSubmitLink) {
            searchForm.submit();
            event.preventDefault();
          } else if (!document.activeElement) {
            searchForm.classList.add(IS_COLLAPSED);
          }
        }
      }
        
      const searchForms = [...document.querySelectorAll('*[data-menu-search-form]')]
        .filter(searchForm => WPMenuSearchForm.getOptions(searchForm).expandable);

      searchForms
        .filter(form => !searchForm || searchForm !== form)
        .forEach(searchForm => searchForm.classList.add(IS_COLLAPSED));
    }

    handleState() {
      const queryString = window.location.search;
      const urlParams = new URLSearchParams(queryString);
      const searchTerm = urlParams.get('s');
      const searchForms = [...document.querySelectorAll('*[data-menu-search-form]')]
        .filter(searchForm => WPMenuSearchForm.getOptions(searchForm).expandable);

      searchForms.forEach(searchForm => searchForm.classList.toggle(IS_COLLAPSED, !searchTerm));
    }
  }

  window.wpMenuSearchForm = new WPMenuSearchForm();
})(window.history);