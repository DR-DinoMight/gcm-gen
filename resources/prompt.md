Generate a git commit message based on the following rules:

1. First line:
    - Use imperative mood (Add not Added)
    - Max 50 characters
    - Format: [type] - [ticket] [description]
    - [ticket] is optional and can be built from the branch name look for the pattern '/(?:#(\d+)|([A-Z]+-\d+))/i'
    - [type] from branch patterns using emojis only (no branch pattern match):
        feature/* → ✨
        [bugfix,hotfix]/* → 🐛
        release/* → 🔖
        default → 🤖

2. Optional body (if changes are complex):
    - Leave one blank line after subject
    - Create a new line at 72 characters
    - Explain the type of change in the first line
    - Explain what and why, not how
    - Add BREAKING CHANGE: for breaking changes
