export default function breadcrumbs() {
  return {
    breadcrumbs: [],
    linkClassName: 'text-gray-500 hover:text-gray-800',
    separatorClassName: 'text-gray-300 ml-4 select-none',
    containerClassName: 'px-5 py-3 rounded flex flex-wrap bg-gray-100 text-sm',
    separator: '/',
    
    init() {
      // If breadcrumbs are passed via x-data, use those
      if (this.$root.dataset.breadcrumbs) {
        this.breadcrumbs = JSON.parse(this.$root.dataset.breadcrumbs);
      }
    },
    
    renderLink(breadcrumb) {
      return `
        <a 
          href="${breadcrumb.url}" 
          class="${this.linkClassName}"
          ${breadcrumb.is_current_page ? 'aria-current="page"' : ''}
        >
          ${breadcrumb.title}
        </a>
      `;
    },
  }
} 