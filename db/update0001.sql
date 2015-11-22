CREATE TABLE addresses (id INTEGER PRIMARY KEY, ns TEXT, page TEXT, address TEXT, mask TEXT, description TEXT);
CREATE INDEX idx_address ON addresses(address);
CREATE INDEX idx_page ON addresses(page);