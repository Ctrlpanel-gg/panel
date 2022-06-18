Export diff files:

git diff -r --no-commit-id --name-only --diff-filter=ACMR <commit> | tar -czf file.tgz -T -
