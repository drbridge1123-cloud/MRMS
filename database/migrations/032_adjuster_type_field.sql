-- Add type field to adjusters table
ALTER TABLE adjusters
    ADD COLUMN adjuster_type ENUM('pip','um','uim','3rd_party','liability','pd','bi') NULL AFTER title;

CREATE INDEX idx_adjusters_type ON adjusters(adjuster_type);
