import { sqliteTable, text } from 'drizzle-orm/sqlite-core';

// Single-association settings. Placeholder until the membership model is designed.
export const setting = sqliteTable('setting', {
	key: text('key').primaryKey(),
	value: text('value').notNull()
});
