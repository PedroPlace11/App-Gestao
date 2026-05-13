// Global type-like definitions for the application in plain JavaScript.
// Use JSDoc typedefs to keep editor autocomplete/intellisense in JS/Vue files.

/** @typedef {'client' | 'supplier' | 'both'} EntityType */

/**
 * @typedef {Object} CountryOption
 * @property {number} id
 * @property {string} name
 * @property {string} code
 * @property {boolean=} active
 */

/**
 * @typedef {Object} Entity
 * @property {number=} id
 * @property {EntityType} type
 * @property {string} nif
 * @property {string} name
 * @property {string} address
 * @property {string} postal_code
 * @property {string} city
 * @property {number|null} country_id
 * @property {string|null=} phone
 * @property {string|null=} mobile
 * @property {string|null=} website
 * @property {string|null=} email
 * @property {boolean=} rgpd_consent
 * @property {string|null=} observations
 * @property {boolean=} active
 * @property {string=} created_at
 * @property {string=} updated_at
 */

/**
 * @template T
 * @typedef {Object} PaginatedResponse
 * @property {T[]=} data
 * @property {{
 *   current_page: number,
 *   from: number,
 *   last_page: number,
 *   path: string,
 *   per_page: number,
 *   to: number,
 *   total: number,
 * }=} meta
 */

export const ENTITY_TYPES = ['client', 'supplier', 'both'];
