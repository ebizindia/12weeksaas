-- Migration: Add recurring field to tasks table
-- Date: 2026-01-14
-- Description: Adds is_recurring field to enable tasks to be automatically copied to future weeks

-- Add is_recurring column to tasks table
-- Using NOT NULL DEFAULT 0 ensures all existing tasks automatically become non-recurring
-- This makes the migration backward-compatible with zero data loss
ALTER TABLE tasks
ADD COLUMN is_recurring TINYINT(1) NOT NULL DEFAULT 0
COMMENT 'Flag indicating if task should recur in future weeks (0=one-time, 1=recurring)'
AFTER weekly_target;

-- Add index for better query performance when filtering recurring tasks
-- This is safe to run even with existing data
CREATE INDEX idx_tasks_recurring ON tasks(is_recurring, goal_id, week_number);
