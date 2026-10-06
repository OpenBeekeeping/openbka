import { index, integer, sqliteTable, text } from 'drizzle-orm/sqlite-core';

// Single-association settings. Placeholder until the membership model is designed.
export const setting = sqliteTable('setting', {
	key: text('key').primaryKey(),
	value: text('value').notNull()
});

// People who have signed in, keyed by their Open Beekeeping account (`sub`).
export const user = sqliteTable('user', {
	id: text('id')
		.primaryKey()
		.$defaultFn(() => crypto.randomUUID()),
	sub: text('sub').notNull().unique(),
	email: text('email').notNull(),
	name: text('name'),
	createdAt: integer('created_at', { mode: 'timestamp_ms' })
		.notNull()
		.$defaultFn(() => new Date()),
	updatedAt: integer('updated_at', { mode: 'timestamp_ms' })
		.notNull()
		.$defaultFn(() => new Date())
});

export const session = sqliteTable(
	'session',
	{
		// SHA-256 of the cookie token, so a database leak doesn't expose live sessions.
		id: text('id').primaryKey(),
		userId: text('user_id')
			.notNull()
			.references(() => user.id, { onDelete: 'cascade' }),
		// Sent back to the accounts service on sign-out (id_token_hint).
		idToken: text('id_token'),
		expiresAt: integer('expires_at', { mode: 'timestamp_ms' }).notNull(),
		createdAt: integer('created_at', { mode: 'timestamp_ms' })
			.notNull()
			.$defaultFn(() => new Date())
	},
	(table) => [index('session_user_id_idx').on(table.userId)]
);
