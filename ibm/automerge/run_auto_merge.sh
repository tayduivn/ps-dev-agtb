#!/bin/sh
####################################################
# Script to merge feature branches from ibm on top
# of the ibm branch into a seperate branch
####################################################

# base stuff
DIR_SCRIPT=`pwd`
DIR_TO_BASE="../.."
BIN_MAIL="/bin/mail"

# settings which can be overriden by cli options
DIFF_TOOL="kdiff3" 				# -d xxx
MODE="interactive" 				# -a (auto mode)
BRANCH_TARGET="ibm_current"		# -t target branch name
FLUSH_TARGET="no"				# -f
BRANCH_BASE="ibm"				# -b xxx
GIT_REMOTE="origin"				# -r xxx
BRANCH_LIST_FILE="$DIR_SCRIPT/branch_list.txt" # -l xxx
GIT_URL="ssh://git@github.com/sugarcrm/Mango.git" # -u git_url
NOTIFY="no"						# -n

# track merge status
MERGE_OK=""
MERGE_NOK=""

# function to die if last cmd was unsuccesful
check_cmd_status() {
	if [ ! $? -eq 0 ]; then
		echo "failed"
		exit 1
	fi
	sleep 1
	echo "ok"
}

# parse cli options
while getopts "hd:t:afb:r:l:u:n" opt; do
	case $opt in
		d) DIFF_TOOL=$OPTARG;;
		t) BRANCH_TARGET=$OPTARG;;
		a) MODE="auto";;
		f) FLUSH_TARGET="flush";;
		b) BRANCH_BASE=$OPTARG;;
		r) GIT_REMOTE=$OPTARG;;
		l) BRANCH_LIST_FILE=$OPTARG;;
		u) GIT_URL=$OPTARG;;
		n) NOTIFY="yes";;
		*) 	echo "";
			echo " Possible options :";
			echo "  -h          Show this help screen";
			echo "  -a          Enable automatic mode, skipping failed merges";
			echo "  -d tool     Specify the difftool to use in interactive mode (default kdiff3)";
			echo "  -t branch   Override the target branch (default ibm_current)";
			echo "  -f          Flush the target branch locally and remotely";
			echo "  -b branch   Override base branch (default ibm)";
			echo "  -r remote   Override remote name (default origin)";
			echo "  -l file     Override branch list file (default branch_list_txt)";
			echo "  -u giturl   Override git push url (default ssh://git@github.com/sugarcrm/Mango.git)";
			echo "  -n          Send email notifications (settings in email_settings.conf)";
			echo "";
			exit 1;;
	esac
done

# lets get things started
echo "###### START AUTO BRANCH MERGER for IBM ######"
echo ""
echo "*** Preflight checks ***"

# determine absolute base path
cd $DIR_TO_BASE
DIR_BASE=`pwd`
echo "Using base dir $DIR_BASE"

# check if difftool exists in interactive mode
if [ "$MODE" == "interactive" ]; then
	echo -e "Searching for difftool $DIFF_TOOL ... \c"
	which $DIFF_TOOL &> /dev/null
	check_cmd_status
fi

# load branch list from file
IFS_OLD=$IFS
IFS=$'\n'
echo -e "Loading branches to be merged ... \c"
BRANCH_LIST=($(cat $BRANCH_LIST_FILE))
check_cmd_status
IFS=$IFS_OLD

# check how many branches we have
if [ ${#BRANCH_LIST[@]} -eq 0 ]; then
	echo "No branches found in $BRANCH_LIST_FILE !"
	exit 1
fi

# add base branch to the list
BRANCH_LIST[${#BRANCH_LIST[@]}]=$BRANCH_BASE

echo "Loaded ${#BRANCH_LIST[@]} branches:"
for branch in ${BRANCH_LIST[@]}; do
	echo " -> $branch"
done

# load email settings
if [ "$NOTIFY" == "yes" ]; then
	echo -e "Searching email executable $BIN_MAIL ... \c"
	which $BIN_MAIL &> /dev/null
	check_cmd_status

	echo -e "Loading email notification config ... \c"
    cat $DIR_SCRIPT/email_settings.conf &> /dev/null
	check_cmd_status
	source $DIR_SCRIPT/email_settings.conf
fi

# check for git repo
echo -e "Checking for git repo ... \c"
git status &> /dev/null
check_cmd_status

# check if remote is defined and pointing to the correct url
echo -e "Checking git remote \"${GIT_REMOTE}\" ... \c"
git remote show $GIT_REMOTE | grep "Push  URL: $GIT_URL" &> /dev/null
check_cmd_status

# fetch from origin to update our refs
echo -e "Fetching from remote \"${GIT_REMOTE}\" ... \c"
git fetch $GIT_REMOTE &> /dev/null
check_cmd_status

# be sure our local base exists
echo -e "Checking base branch \"${BRANCH_BASE}\" ... \c"
git checkout $BRANCH_BASE &> /dev/null
check_cmd_status

# pull from base
echo -e "Pulling latest changes for \"${BRANCH_BASE}\" ... \c"
git pull $GIT_REMOTE &> /dev/null
check_cmd_status

# if we flush the target, we recreate it from the base branch
if [ "$FLUSH_TARGET" == "flush" ]; then

	# flush local/remote target branch
	git push $GIT_REMOTE :$BRANCH_TARGET &> /dev/null
	git branch -D $BRANCH_TARGET &> /dev/null

	# create local target branch
	echo -e "Creating target branch \"${BRANCH_TARGET}\" ... \c"
	git checkout -b $BRANCH_TARGET &> /dev/null
	check_cmd_status

# if we don't flush checkout the target branch from remote or create it if non-exist
else

	# remove the local target branch in case it exists
	git branch -D $BRANCH_TARGET &> /dev/null

	# check if our target branch already exists on remote
	echo -e "Checking for \"$BRANCH_TARGET\" on \"$GIT_REMOTE\" ... \c"
	git branch -r | grep "${GIT_REMOTE}/${BRANCH_TARGET}$" &> /dev/null
	if [ $? -eq 0 ]; then
		echo "ok"
		echo -e "Checking out target branch \"$BRANCH_TARGET\" from \"$GIT_REMOTE\" ... \c"
		git checkout -b $BRANCH_TARGET $GIT_REMOTE/$BRANCH_TARGET &> /dev/null
		check_cmd_status
	else
		echo "not found"
		echo -e "Creating new target branch \"$BRANCH_TARGET\" for \"$GIT_REMOTE\" ... \c"
		git checkout -b $BRANCH_TARGET &> /dev/null
		check_cmd_status
	fi

fi

echo "*** Preflight checks done ***"
echo

# just be sure we are in our target branch
cd $DIR_BASE
echo -e "*** Switching to target branch ... \c"
git checkout $BRANCH_TARGET &> /dev/null
check_cmd_status

# check if branches exist on remote
for branch in ${BRANCH_LIST[@]}; do
	echo ""
	echo "*** AutoMerging branch \"${branch}\" ***"
		
	# check if remote branch exists
	echo -e "Searching for $GIT_REMOTE/$branch ... \c"
	git branch -r | grep "${GIT_REMOTE}/${branch}$" &> /dev/null
	check_cmd_status

	# merge remote branch
	CLEAN_MERGE="ok"
	echo -e "Trying to merge \"$branch\" into \"$BRANCH_TARGET\" ... \c"
	git merge --no-ff --no-commit ${GIT_REMOTE}/${branch} &> /dev/null
	if [ ! $? -eq 0 ]; then
		CLEAN_MERGE="failed"
	fi
	echo $CLEAN_MERGE

	# ignore the readme file
	git reset HEAD readme.txt &> /dev/null
	git checkout -- readme.txt &> /dev/null

	if [ "$CLEAN_MERGE" == "ok" ]; then

		# log
		MERGE_OK="$MERGE_OK\n$branch"

		# extra comments for base branch
		if [ "$branch" == "$BRANCH_BASE" ]; then
			MERGE_OK="$MERGE_OK - base_branch"
		fi

		# commit the merge
		echo -e "Committing merge ... \c"
		git commit -m "AUTOMERGER: merge $branch" &> /dev/null
		if [ ! $? -eq 0 ]; then
			echo "no changes"
			MERGE_OK="$MERGE_OK (no changes)"
		else
			echo "ok"
		fi

	else

		# in automatic mode we just skip current feature branch and go on
		if [ "$MODE" == "auto" ]; then
			
			# log
			MERGE_NOK="$MERGE_NOK\n$branch"

			# extra comments for base branch
			if [ "$branch" == "$BRANCH_BASE" ]; then
				MERGE_NOK="$MERGE_NOK - base_branch"
			fi

			# forget this merge
			echo -e "Skipping this merge ... \c"
			git reset --hard &> /dev/null
			check_cmd_status

		# in interactive mode use our difftool to resolve the conflicts
		else

			# use the mergetool to manually resolve conflicts
			git mergetool -y -t $DIFF_TOOL &> /dev/null
		
			# check if all conflicts are resolved
			echo -e "Checking if all conflicts are resolved ... \c"
			git status -s | grep "^UU" &> /dev/null
			if [ $? -eq 0 ]; then
				echo "failed"
				MERGE_NOK="$MERGE_NOK\n$branch"

				# extra comments for base branch
				if [ "$branch" == "$BRANCH_BASE" ]; then
					MERGE_NOK="$MERGE_NOK - base_branch"
				fi

				# forget this merge
				echo -e "Skipping this merge ... \c"
				git reset --hard &> /dev/null
				check_cmd_status
			else
				echo "ok"
				MERGE_OK="$MERGE_OK\n$branch"
				
				# extra comments for base branch
				if [ "$branch" == "$BRANCH_BASE" ]; then
					MERGE_OK="$MERGE_OK - base_branch"
				fi

				# commit the merge
				echo -e "Committing merge ... \c"
				git commit -m "AUTOMERGER: merge $branch (manually resolved conflicts)" &> /dev/null
				if [ ! $? -eq 0 ]; then
					echo "no changes"
					MERGE_OK="$MERGE_OK (no changes)"
				else
					echo "ok"
					MERGE_OK="$MERGE_OK (manually resolved conflicts)"
				fi

			fi # conflicts resolved

		fi # auto mode

	fi # clean merge

done

# push our target branch to the remote
echo ""
echo "*** Post merge tasks ***"

# setup readme file
echo -e "Setting up readme ... \c"
TIMESTAMP=`date`
if [ "$MERGE_OK" == "" ]; then
	MERGE_OK="none"
fi
if [ "$MERGE_NOK" == "" ]; then
	MERGE_NOK="none"
fi
echo "" > readme.txt
echo "*** AUTO MERGER RESULTS ***" >> readme.txt
echo "Timestamp: $TIMESTAMP" >> readme.txt
echo "Base branch: $BRANCH_BASE" >> readme.txt
echo "Target branch: $BRANCH_TARGET" >> readme.txt
echo "Mode: $MODE" >> readme.txt
echo "Flush: $FLUSH_TARGET" >> readme.txt
echo -e "\nSuccesfully merged branches:\n$MERGE_OK" >> readme.txt
echo -e "\nFailed (skipped) branches:\n$MERGE_NOK\n" >> readme.txt
git add readme.txt
git commit -m "AUTOMERGER: updating status" &> /dev/null
check_cmd_status

# push local target branch to remote
echo -e "Pushing $BRANCH_TARGET to $GIT_REMOTE ... \c"
git push $GIT_REMOTE $BRANCH_TARGET:$BRANCH_TARGET &> /dev/null
check_cmd_status

# send email notifications
if [ "$NOTIFY" == "yes" ]; then
	echo -e "Sending email notifications ... \c"
	cat readme.txt | /bin/mail -r "$MAIL_FROM" -c "$MAIL_CC" -s "$MAIL_SUBJECT - $BRANCH_TARGET" "$MAIL_TO"
	check_cmd_status
fi

# finish 
echo ""
echo "###### END - AUTO BRANCH MERGER for IBM ######"
cd $DIR_SCRIPT
exit 0
