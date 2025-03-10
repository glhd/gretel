import React from 'react';

export default function Breadcrumbs({ 
  breadcrumbs = [],
  className = '',
  linkClassName = 'text-gray-500 hover:text-gray-800',
  separatorClassName = 'text-gray-300 ml-4 select-none',
  containerClassName = 'px-5 py-3 rounded flex flex-wrap bg-gray-100 text-sm',
  separator = '/',
  renderLink = ({ href, children, isCurrentPage }) => (
    <a 
      href={href} 
      className={linkClassName}
      {...(isCurrentPage ? { 'aria-current': 'page' } : {})}
    >
      {children}
    </a>
  ),
}) {
  if (!breadcrumbs?.length) return null;

  return (
    <nav aria-label="Breadcrumb">
      <ol className={containerClassName}>
        {breadcrumbs.map((breadcrumb, index) => (
          <li 
            key={breadcrumb.url}
            className={index !== breadcrumbs.length - 1 ? 'mr-4' : ''}
          >
            <div className="flex items-center">
              {renderLink({
                href: breadcrumb.url,
                children: breadcrumb.title,
                isCurrentPage: breadcrumb.is_current_page,
              })}
              {index !== breadcrumbs.length - 1 && (
                <span 
                  aria-hidden="true" 
                  className={separatorClassName}
                >
                  {separator}
                </span>
              )}
            </div>
          </li>
        ))}
      </ol>
    </nav>
  );
} 