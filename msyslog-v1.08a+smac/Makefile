# $CoreSDI: Makefile.in,v 1.16.2.3.4.6 2001/11/20 09:56:21 alejo Exp $
#
# Copyright (c) 2001, Core SDI S.A., Argentina
# All rights reserved
# 
# Redistribution and use in source and binary forms, with or without
# modification, are permitted provided that the following conditions
# are met:
# 
# 1. Redistributions of source code must retain the above copyright
#    notice, this list of conditions and the following disclaimer.
# 2. Redistributions in binary form must reproduce the above copyright
#    notice, this list of conditions and the following disclaimer in the
#    documentation and/or other materials provided with the distribution.
# 3. Neither name of the Core SDI S.A. nor the names of its contributors
#    may be used to endorse or promote products derived from this software
#    without specific prior written permission.
#
# THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
# IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
# OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
# IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
# INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
# NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
# DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
# THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
# (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF
# THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
#

MODULES_SUBDIR = src/modules
SUBDIRS = src/peo $(MODULES_SUBDIR) src src/man
INSTALL_SUBDIRS = src/modules src/peo src/man src 
RELEASE=        msyslog-v@MSYSLOG_VERSION@
TARBALL=        ${RELEASE}-src.tar.gz


all: all-recursive

all-recursive clean-recursive distclean-recursive:
	@target=`echo $@ | sed s/-recursive//`; \
	list='$(SUBDIRS)'; \
	for subdir in $$list; do \
		(cd $$subdir && exec $(MAKE) $$target) \
	done

install-recursive:
	@target=`echo $@ | sed s/-recursive//`; \
	list='$(INSTALL_SUBDIRS)'; \
	for subdir in $$list; do \
		(cd $$subdir && exec $(MAKE) $$target) \
	done

modules:
	cd $(MODULES_SUBDIR) && make all

modules-install:
	cd $(MODULES_SUBDIR) && make install


.PHONY: clean distclean install
clean: clean-recursive
install: all install-recursive

distclean: distclean-recursive
	-rm -f Makefile config.cache config.log config.status

dist distribution: cleandist
	@DIR=`pwd` && cd .. && tar -z -c -s '/$$DIR/${RELEASE}/' \
	    -f $$DIR/${TARBALL} \
	    `find $$DIR -type f -print | egrep -v "ports|CVS|*gz"` && \   
	    cd - && echo "Distribution file ${TARBALL} has been created."  
