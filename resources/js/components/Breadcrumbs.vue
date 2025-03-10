<template>
  <nav v-if="breadcrumbs.length" aria-label="Breadcrumb">
    <ol :class="containerClassName">
      <li 
        v-for="(breadcrumb, index) in breadcrumbs" 
        :key="breadcrumb.url"
        :class="{ 'mr-4': index !== breadcrumbs.length - 1 }"
      >
        <div class="flex items-center">
          <component
            :is="renderLink"
            :href="breadcrumb.url"
            :class="linkClassName"
            :aria-current="breadcrumb.is_current_page ? 'page' : undefined"
          >
            {{ breadcrumb.title }}
          </component>
          <span 
            v-if="index !== breadcrumbs.length - 1"
            aria-hidden="true" 
            :class="separatorClassName"
          >
            {{ separator }}
          </span>
        </div>
      </li>
    </ol>
  </nav>
</template>

<script>
export default {
  name: 'Breadcrumbs',
  
  props: {
    breadcrumbs: {
      type: Array,
      default: () => [],
    },
    linkClassName: {
      type: String,
      default: 'text-gray-500 hover:text-gray-800',
    },
    separatorClassName: {
      type: String,
      default: 'text-gray-300 ml-4 select-none',
    },
    containerClassName: {
      type: String,
      default: 'px-5 py-3 rounded flex flex-wrap bg-gray-100 text-sm',
    },
    separator: {
      type: String,
      default: '/',
    },
    renderLink: {
      type: Function,
      default: ({ href, children, isCurrentPage }) => ({
        template: `<a :href="href" :class="className" :aria-current="isCurrentPage ? 'page' : undefined">${children}</a>`,
        props: ['href', 'className', 'isCurrentPage', 'children'],
      }),
    },
  },
}
</script> 