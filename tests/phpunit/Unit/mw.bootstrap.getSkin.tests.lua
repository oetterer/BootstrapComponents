--[[
	Tests for mw.bootstrap.getSkin module

	@since 1.0

	@licence GNU GPL v3+
	@author Tobias Oetterer
]]

local testframework = require 'Module:TestFramework'

-- Tests
local tests = {
	{
		name = 'mw.bootstrap.parse.getSkin.lua: getSkin',
		func = mw.bootstrap.getSkin,
		args = { nil },
		expect = { 'vector' }
	},
}

return testframework.getTestProvider( tests )
